import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.markercluster';
import domready from 'mf-js/modules/dom/ready';

domready(() => {
  L.Icon.Default.imagePath = '/media/favicon/';
  const mapElement = document.getElementById('mapId');

  const mapCommunaute = L.map('mapId').setView([51.505, -0.09], 5);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors',
    maxZoom: 18
  }).addTo(mapCommunaute);


  console.log(members);
  
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
  members.forEach(member => {
    if(member.latitude != null & member.longitude != null) {
      const marker = L.marker([member.latitude, member.longitude]);
      marker.bindPopup(`<b>${member.name}</b><br />${member.description}`).openPopup();
      markers.addLayer(marker);
    }
  });
  

  mapCommunaute.addLayer(markers);
});


