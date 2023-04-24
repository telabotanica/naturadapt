import L from 'leaflet';
import domready from 'mf-js/modules/dom/ready';

let geojsonLayer; // Déclarez la variable en dehors de la fonction

function getColor(membersCount) {
  return membersCount == 1 ? '#08306b' :
         membersCount > 500  ? '#08519c' :
         membersCount > 200  ? '#2171b5' :
         membersCount > 100  ? '#4292c6' :
         membersCount == 1   ? '#6baed6' :
         membersCount == 0  ? '#9ecae1' :
                               '#c6dbef';
}

async function loadRegionsLayer(map, zoomLevel, membersDataPromise) {
  // Retirez la couche de tuiles précédente (si elle existe)
  if (geojsonLayer) {
    map.removeLayer(geojsonLayer);
  }

  const geojsonUrl = zoomLevel <= 3 ? '/data/countries.geojson' : '/data/NUTS_RG_60M_2021_4326.geojson';
  const response = await fetch(geojsonUrl);
  const data = await response.json();

  const membersData = await membersDataPromise;
  // Initialisez geojsonLayer avec les données récupérées et la fonction de style adaptée
  geojsonLayer = L.geoJSON(data, {
    style: (feature) => style(feature, membersData, zoomLevel),
  });

  // Ajoutez la nouvelle couche de tuiles GeoJSON à la carte
  map.addLayer(geojsonLayer);
}

  
function style(feature, membersData, zoomLevel) {

  console.log('membersData', membersData);

  const levelCode = feature.properties["LEVL_CODE"];
  // Note: Plus le zoomLevel est élévé plus le niveau de détail est grand
  
  let count = 0;
  
  // Le niveau de Zoom défini les régions plus ou moins petite à colorer sur la carte
  let levelCodeToKeep;
  if(zoomLevel > 5) {
    levelCodeToKeep = 3;
  } else {
    levelCodeToKeep = 2;
  }

  if (levelCodeToKeep == 2) {
    const level2RegionCode = feature.properties.NUTS_ID.substring(0, 4);
    if (membersData.level2.countByRegion.hasOwnProperty(level2RegionCode)) {
      count = membersData.level2.countByRegion[level2RegionCode];
    }
  } else {
      const level3RegionCode = feature.properties.NUTS_ID;
      if (membersData.level3.countByRegion.hasOwnProperty(level3RegionCode)) {
        count = membersData.level3.countByRegion[level3RegionCode];
      }
  }
  let fillColor = null
  if(levelCode === levelCodeToKeep) {
    fillColor = getColor(count);
      return {
        fillColor: fillColor,
        weight: 1,
        opacity: 1,
        color: 'white',
        fillOpacity: 0.7,
      };
  } else {
 
    return {
      weight: 2,
      opacity: 1,
      color: 'white',
      dashArray: '3',
      fillOpacity: 0
    };
  }

}
      
async function fetchMapData() {
  const response = await fetch('/map_data');
  const membersDataLevel3 =  await response.json();

  // Calculer les données de niveau 2
  const membersDataLevel2 = {};
  for (const countryCode in membersDataLevel3) {
    for (const regionId in membersDataLevel3[countryCode]) {
      const level2RegionId = regionId.substring(0, 4);
      if (!membersDataLevel2[countryCode]) {
        membersDataLevel2[countryCode] = {};
      }
      if (!membersDataLevel2[countryCode][level2RegionId]) {
        membersDataLevel2[countryCode][level2RegionId] = 0;
      }
      membersDataLevel2[countryCode][level2RegionId] += membersDataLevel3[countryCode][regionId];
    }
  }

  return {
    level2: membersDataLevel2,
    level3: membersDataLevel3,
  };
}

// Fonction principale exécutée lorsque le DOM est prêt
domready(async () => {
    const mapElement = document.getElementById('mapHomeRegionId');
    if (mapElement) {
      const initialZoom = 5;
      // initMap();

      const mapRegions = L.map('mapHomeRegionId', {
        minZoom: 2, // Niveau de zoom minimal
        maxZoom: 10, // Niveau de zoom maximal
        zoomSnap: 2.5, // Niveau de zoom auquel la carte s'accroche
      }).setView([51.505, -0.09], initialZoom);
  
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors',
        minZoom: 2, // Niveau de zoom minimal pour la couche de tuiles
        maxZoom: 10, 
      }).addTo(mapRegions);
  
      // const membersCountByRegion = getMembersCountByRegion();
      // Chargez les données de la carte
      const membersDataPromise = await fetchMapData();

      // Appeler la fonction loadRegionsLayer avec le niveau de zoom initial (2)
      loadRegionsLayer(mapRegions, initialZoom, membersDataPromise);

      // Mettre à jour la couche des régions lors d'un changement de zoom
      mapRegions.on('zoomend', function () {
        loadRegionsLayer(mapRegions, mapRegions.getZoom(), membersDataPromise);
      });
    }
});


