// Import des librairies et des fichiers nécessaires
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.markercluster';
import domready from 'mf-js/modules/dom/ready';
import '../../css/map/map-communaute.scss';
import '../../css/map/map-home.scss';

// Fonction pour créer une icône personnalisée en fonction de la couleur et de l'URL de l'avatar
async function getCustomIcon(color, avatarUrl=null) {
  const defaultIcon = `<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="16" height="16" viewBox="0 0 16 16">
                          <use xlink:href="#user"/>
                        </svg>`;

  // Si aucune URL d'avatar n'est fournie, retournez une icône par défaut
  if (!avatarUrl) {
    return L.divIcon({
      html: `<div class="custom-marker" style="background-color: ${color};">
              ${defaultIcon}
            </div>`,
      className: '',
      iconSize: [30, 30],
      iconAnchor: [15, 15],
    });
  }

  // Fonction pour charger une image et retourner une promesse
  const loadImage = (url) => {
    return new Promise((resolve, reject) => {
      const img = new Image();
      img.src = url;
      img.onload = () => resolve(url);
      img.onerror = () => reject();
    });
  };

  // Essayez de charger l'image de l'avatar, si elle existe, retournez l'icône avec l'image de l'avatar
  // Sinon, retournez l'icône avec un cercle blanc
  try {
    await loadImage(avatarUrl);
    return L.divIcon({
      html: `<div class="custom-marker" style="background-color: ${color};">
              <div class="marker-avatar" style="background-image: url('${avatarUrl}');"></div>
            </div>`,
      className: '',
      iconSize: [30, 30],
      iconAnchor: [15, 15],
    });
  } catch {
    return L.divIcon({
      html: `<div class="custom-marker" style="background-color: #ffffff;">
              <div class="marker-avatar" style="background-color: #ffffff;"></div>
            </div>`,
      className: '',
      iconSize: [30, 30],
      iconAnchor: [15, 15],
    });
  }
}

// Function pour filtrer entre les démarches adaptatives et tout le monde
function filterMarkers(markersCluster, markersArray, showAdaptive) {
  markersCluster.clearLayers(); // Retirez tous les marqueurs du groupe de clusters
  markersArray.forEach((marker) => {
      if (!marker) return;

      if (!showAdaptive || marker.options.hasAdaptiveApproach) {
          markersCluster.addLayer(marker); // Ajoutez les marqueurs qui correspondent au filtre
      }
  });
}

function showAdaptiveMarkers(showAdaptiveOnly) {
  markers.clearLayers(); // Effacer tous les marqueurs existants

  // Parcourez la liste des membres et ajoutez un marqueur pour chaque membre
  members.forEach(async (member) => {
    if (member.latitude != null && member.longitude != null) {
      // Si on montre seulement les démarches d'adaptation et que le membre n'a pas de démarche d'adaptation, passer
      if (showAdaptiveOnly && !member.hasAdaptativeApproach) return;

      // Obtenez l'icône personnalisée pour chaque membre
      const icon = await getCustomIcon('#ffffff', null);

      // Créez un marqueur avec l'icône personnalisée et ajoutez-le au groupe de marqueurs
      const marker = L.marker([member.latitude, member.longitude], { icon: icon });
      let popupContent = `<b>${member.name}</b>`;
      if (member.hasAdaptativeApproach) {
        if(member.adaptativeApproachDescription && member.adaptativeApproachLink) {
          popupContent += `<br/><a href='${member.adaptativeApproachLink}' target="_blank">${member.adaptativeApproachDescription}</a>`;
        } else if(member.adaptativeApproachDescription) {
          popupContent += `<br/>${member.adaptativeApproachDescription}`;
        } else if (member.adaptativeApproachLink) {
          popupContent += `<br/><a href='${member.adaptativeApproachLink}' target="_blank">${member.adaptativeApproachLink}</a>`;
        }     
      }
      marker.bindPopup(popupContent).openPopup();
      markers.addLayer(marker);
    }
  });
}


// Fonction principale exécutée lorsque le DOM est prêt
domready(async () => {
  // Configuration du chemin de l'image par défaut pour les icônes de Leaflet
  L.Icon.Default.imagePath = '/media/favicon/';
  const mapElement = document.getElementById('mapHomeId');
  if (mapElement) { 
    // Création de la carte avec les coordonnées et le niveau de zoom initiaux
    const mapCommunaute = L.map('mapHomeId').setView([46.7111, 1.7191], 5);

    // Ajout de la couche de tuiles OpenStreetMap à la carte
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors',
      maxZoom: 18
    }).addTo(mapCommunaute);

    // Configuration du regroupement de marqueurs
    var markers = L.markerClusterGroup({
      iconCreateFunction: function(cluster) {
        // Calculez le nombre de marqueurs dans le cluster
        const count = cluster.getChildCount();

        // Créez une icône pour représenter le cluster
        return L.divIcon({
          html: count,
          className: 'mycluster',
          iconSize: null
        });
      }
    });
    // Parcourez la liste des membres et ajoutez un marqueur pour chaque membre
    const markerPromises = members.map(async (member) => {
      if (member.latitude != null && member.longitude != null) {

        // Obtenez l'icône personnalisée pour chaque membre
        const icon = await getCustomIcon('#ffffff', null);

        // Créez un marqueur avec l'icône personnalisée et ajoutez-le au groupe de marqueurs
        const marker = L.marker([member.latitude, member.longitude], { icon: icon, hasAdaptiveApproach: member.hasAdaptativeApproach, });
        let popupContent = `<b>${member.name}</b>`;
        if (member.hasAdaptativeApproach) {
          if(member.adaptativeApproachDescription && member.adaptativeApproachLink) {
            popupContent += `<br/><a href='${member.adaptativeApproachLink}' target="_blank">${member.adaptativeApproachDescription}</a>`;
          } else if(member.adaptativeApproachDescription) {
            popupContent += `<br/>${member.adaptativeApproachDescription}`;
          } else if (member.adaptativeApproachLink) {
            popupContent += `<br/><a href='${member.adaptativeApproachLink}' target="_blank">${member.adaptativeApproachLink}</a>`;
          }     
        }
        marker.bindPopup(popupContent).openPopup();
        markers.addLayer(marker);
        return marker;
      }
      return null;
    });

    // Attendez que tous les marqueurs soient chargés
    await Promise.all(markerPromises);

    const adaptativeToggle = document.getElementById("adaptative-toggle");
    const toggleSwitchText = document.getElementById("toggle-switch-text");
    const toggleSwitchLabel = document.getElementById("toggle-switch-label");
    
    async function updateMarkers(checked) {
      const allMarkers = await Promise.all(markerPromises);
      filterMarkers(markers, allMarkers, checked);
    }

    // Initialisation de l'état des marqueurs
    updateMarkers(adaptativeToggle.checked);

    adaptativeToggle.addEventListener("change", async (event) => {
      updateMarkers(event.target.checked);

      // Mettre à jour le texte à côté du switch
      if (adaptativeToggle.checked) {
        toggleSwitchText.textContent = "Montrer tous les utilisateurs";
      } else {
        toggleSwitchText.textContent = "Montrer les utilisateurs avec une démarches d'adaptation";
      }
    });


    toggleSwitchLabel.addEventListener("click", function (event) {
      event.stopPropagation(); // Empêcher la propagation de l'événement au niveau supérieur (la carte)
    });
    
    // Ajoutez le groupe de marqueurs à la carte
    mapCommunaute.addLayer(markers);
  }
});
