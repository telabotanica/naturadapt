import L from 'leaflet';
import domready from 'mf-js/modules/dom/ready';

function getColor(membersCount) {
  return membersCount > 1000 ? '#08306b' :
         membersCount > 500  ? '#08519c' :
         membersCount > 200  ? '#2171b5' :
         membersCount > 100  ? '#4292c6' :
         membersCount > 50   ? '#6baed6' :
         membersCount > 20   ? '#9ecae1' :
                               '#c6dbef';
}

function style(feature) {
    return {
      fillColor: getColor(feature.properties.membersCount),
      weight: 2,
      opacity: 1,
      color: 'white',
      dashArray: '3',
      fillOpacity: 0.7
    };
  }

function getMembersCountByRegion() {
    // Remplacez cette fonction par une requête à votre API pour obtenir les données réelles
    return {
        'FRA': 100,
        'USA': 500,
        'BRA': 200,
        // ...
    };
}

// Fonction principale exécutée lorsque le DOM est prêt
domready(async () => {
    const mapElement = document.getElementById('mapHomeRegionId');
    if (mapElement) {
      const mapRegions = L.map('mapHomeRegionId').setView([51.505, -0.09], 2);
  
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors',
        maxZoom: 18
      }).addTo(mapRegions);
  
      const membersCountByRegion = getMembersCountByRegion();
  
      fetch('/data/countries.geojson')
      .then(response => response.json())
      .then(data => {
        data.features.forEach(feature => {
          const regionCode = feature.properties.ISO_A3;
          feature.properties.membersCount = membersCountByRegion[regionCode] || 0;
        });
  
        L.geoJSON(data, { style: style }).addTo(mapRegions);
      });
    }
  });


