import L from 'leaflet';
import domready from 'mf-js/modules/dom/ready';
import '../../css/map/map-communaute.scss';
import '../../css/map/map-home.scss';

let geojsonLayer; // Déclarez la variable en dehors de la fonction

function getColor(value) {
  let color;

  if (value === 0) {
    color = 'rgba(255, 255, 255, 0)'; // Completely transparent for 0
  } else if (value === 1) {
    color = `rgba(255, 245, 230, 1)`;
  } else if (value < 10) {
    color = `rgba(255, 215, 180, 1)`;
  } else if (value < 50) {
    color = `rgba(255, 185, 130, 1)`;
  } else if (value < 100) {
    color = `rgba(255, 155, 80, 1)`;
  } else if (value < 250) {
    color = `rgba(255, 125, 30, 1)`;
  } else {
    color = `rgba(230, 75, 0, 1)`; // Darker orange for >= 250
  }

  return color;
}

function getCountByRegion(feature, membersData, levelCodeToKeep, adaptativeChecked) {
  let count = 0;
  const adaptativeString = adaptativeChecked? 'adaptative_approach':'all';

  if (levelCodeToKeep === 1) {
    const countryCode = feature.properties.ISO_A2;
    if (membersData.level1[adaptativeString].hasOwnProperty(countryCode)) {
      count = membersData.level1[adaptativeString][countryCode];
    }
  } else if (levelCodeToKeep === 2) {
    const level2RegionCode = feature.properties.NUTS_ID.substring(0, 4);
    if (membersData.level2[adaptativeString].hasOwnProperty(level2RegionCode)) {
      count = membersData.level2[adaptativeString][level2RegionCode];
    }
  } else if (levelCodeToKeep === 3) {
    const level3RegionCode = feature.properties.NUTS_ID;
    if (membersData.level3[adaptativeString].hasOwnProperty(level3RegionCode)) {
      count = membersData.level3[adaptativeString][level3RegionCode];
    }
  }

  return count;
}



async function loadRegionsLayer(map, zoomLevel, membersDataPromise, adaptativeChecked) {
  // Retirez la couche de tuiles précédente (si elle existe)
  if (geojsonLayer) {
    map.removeLayer(geojsonLayer);
  }

  // LevelCodeToKeep défini le niveau de détail à afficher sur la carte
  // Valeur en fonction du niveau de zoom
  let levelCodeToKeep;
  if(zoomLevel > 5) {
    levelCodeToKeep = 3;
  } else if (zoomLevel > 4) {
    levelCodeToKeep = 2;
  } else {
    levelCodeToKeep = 1;
  }

  const geojsonUrl = levelCodeToKeep == 1 ? '/data/countries.geojson' : '/data/NUTS_RG_60M_2021_4326.geojson';
  const response = await fetch(geojsonUrl);
  const data = await response.json();

  // Filter the GeoJSON data based on the current zoom level
  const filteredData = {
    ...data,
    features: data.features.filter(feature => {
      if(levelCodeToKeep === 1) {
        return true;
      } else {
        const levelCode = feature.properties["LEVL_CODE"];

        if (levelCodeToKeep === levelCode) {
          return true;
        } else {
          return false;
        }
      }

      return false;
    }),
  };

  const membersData = await membersDataPromise;
  // Initialisez geojsonLayer avec les données récupérées et la fonction de style adaptée
  geojsonLayer = L.geoJSON(filteredData, {
    style: (feature) => style(feature, membersData, levelCodeToKeep, adaptativeChecked),
    onEachFeature: function (feature, layer) {
      // Add a mouseover event handler to display the count number
      layer.on('mouseover', function (e) {
        let count = getCountByRegion(feature, membersData, levelCodeToKeep, adaptativeChecked);
        if (count !== 0) {
          layer.bindTooltip(count + " démarches d'adaptations", {sticky: true}).openTooltip();
        }
      });

      // Add a mouseout event handler to hide the tooltip
      layer.on('mouseout', function (e) {
        layer.closeTooltip();
      });
    }
  });

  // Ajoutez la nouvelle couche de tuiles GeoJSON à la carte
  map.addLayer(geojsonLayer);
}

  
function style(feature, membersData, levelCodeToKeep, adaptativeChecked) {
  const count = getCountByRegion(feature, membersData, levelCodeToKeep, adaptativeChecked);
  const fillColor = getColor(count);
  return {
    fillColor: fillColor,
    weight: 1,
    opacity: 1,
    color: 'white',
    fillOpacity: 0.7,
  };

}
      
async function fetchMapData() {
  const response = await fetch('/map_data');
  const membersData =  await response.json();

  // Calculer les données de niveau 2
  const level2Data = {'adaptative_approach': {}, 'all': {}};

  let level3Data = membersData["level3"];
  for(const userOrAdaptativeData in level3Data){
    for (const regionId in level3Data[userOrAdaptativeData]) {
      const level2RegionId = regionId.substring(0, 4);
      if (!level2Data[userOrAdaptativeData][level2RegionId]) {
        level2Data[userOrAdaptativeData][level2RegionId] = 0;
      }
      level2Data[userOrAdaptativeData][level2RegionId] += level3Data[userOrAdaptativeData][regionId];
    }
  }

  return {
    level1: membersData["level1"],
    level2: level2Data,
    level3: membersData["level3"],
  };
}

// Fonction principale exécutée lorsque le DOM est prêt
domready(async () => {
    const mapElement = document.getElementById('mapHomeRegionId');
    if (mapElement) {
      const initialZoom = 5;

      const mapRegions = L.map('mapHomeRegionId', {
        scrollWheelZoom: false, // Désactivez le zoom avec la molette de la souris
        minZoom: 3, // Niveau de zoom minimal
        maxZoom: 6, // Niveau de zoom maximal
        zoomSnap: 1, // Niveau de zoom auquel la carte s'accroche
      }).setView([46.7111, 1.7191], initialZoom);
  
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors',
        minZoom: 3, // Niveau de zoom minimal pour la couche de tuiles
        maxZoom: 6, 
      }).addTo(mapRegions);
  
      // Chargez les données de la carte
      const membersDataPromise = await fetchMapData();


      const adaptativeToggle = document.getElementById("adaptative-toggle");
      const toggleSwitchText = document.getElementById("toggle-switch-text");
      const toggleSwitchLabel = document.getElementById("toggle-switch-label");

      adaptativeToggle.addEventListener("change", (event) => {
        // updateRegionColors(event.target.checked);
        loadRegionsLayer(mapRegions, mapRegions.getZoom(), membersDataPromise, event.target.checked);
        // Mettre à jour le texte à côté du switch
        if (adaptativeToggle.checked) {
          toggleSwitchText.textContent = "Montrer tous les utilisateurs";
        } else {
          toggleSwitchText.textContent = "Montrer les utilisateurs avec une démarches d'adaptation";
        }
      });


      // Appeler la fonction loadRegionsLayer avec le niveau de zoom initial (2)
      loadRegionsLayer(mapRegions, initialZoom, membersDataPromise, adaptativeToggle.checked);





      // Mettre à jour la couche des régions lors d'un changement de zoom
      mapRegions.on('zoomend', function () {
        loadRegionsLayer(mapRegions, mapRegions.getZoom(), membersDataPromise, adaptativeToggle.checked);
      });

      toggleSwitchLabel.addEventListener("click", function (event) {
        event.stopPropagation(); // Empêcher la propagation de l'événement au niveau supérieur (la carte)
      });
    }
});


