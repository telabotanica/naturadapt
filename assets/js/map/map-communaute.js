// Import des librairies et des fichiers nécessaires
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.markercluster';
import domready from 'mf-js/modules/dom/ready';
import '../../css/map/map-communaute.scss';

// Fonction js copiant le fonctionnement de l'extension Twig dans ColorExtension.php
function generateColorFromString(string) {
  const c = string
      .substr(0, 16)
      .split('')
      .reduce((carry, char) => {
          return (carry + char.charCodeAt(0)) % 256;
      }, 0);

  return `hsl(${c}, 80%, 60%)`;
}


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

// Fonction principale exécutée lorsque le DOM est prêt
domready(async () => {
  // Configuration du chemin de l'image par défaut pour les icônes de Leaflet
  L.Icon.Default.imagePath = '/media/favicon/';
  const mapElement = document.getElementById('mapCommunauteId');
  if (mapElement) { 
    // Création de la carte avec les coordonnées et le niveau de zoom initiaux
    const mapCommunaute = L.map('mapCommunauteId').setView([46.7111, 1.7191], 5);

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
        const userId = member.id;
        const avatarElement = document.querySelector(`[data-user-id="${userId}"]`);
        let icon;
        if(avatarElement) {
          const avatarUrl = avatarElement.dataset.avatarUrl;
          const color = avatarElement.dataset.color; 
          // Obtenez l'icône personnalisée pour chaque membre
          icon = await getCustomIcon(color, avatarUrl);
        } else {
          const color = generateColorFromString(member.name)
          icon = await getCustomIcon(color);
        }

        // Créez un marqueur avec l'icône personnalisée et ajoutez-le au groupe de marqueurs
        const marker = L.marker([member.latitude, member.longitude], { icon: icon });
        let popupContent = `<b><a href='/members/${member.id}' target='_blank'>${member.name}</a></b>`;
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

    // Ajoutez le groupe de marqueurs à la carte
    mapCommunaute.addLayer(markers);
  }
});
