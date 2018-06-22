let map, infoWindow;
const markers = [],
    mapDiv = document.getElementById('map'),
    searchDiv = document.getElementById('search-control'),
    searchInput = document.getElementById('search-input'),
    progressDiv = document.getElementById('progress-bar');

/**
 * @typedef {Object} Coords
 * @property {Number} lat
 * @property {Number} lng
 */
/**
 * @typedef {Object} JobItem
 * @property {String} guid
 * @property {String} link
 * @property {String} author
 * @property {String[]} categories
 * @property {String} title
 * @property {String} description
 * @property {String} location
 * @property {String} pubDate
 * @property {Coords} coords
 * @property {?String} salary
 * @property {Boolean} remote
 * @property {?Boolean} visaSponsor
 * @property {?Boolean} paidRelocation
 * @property {?String} company
 */

window['initMap'] = function () {
    map = new google.maps.Map(mapDiv, {
        center: {lat: 29, lng: 0},
        zoom: 3
    });
    infoWindow = new google.maps.InfoWindow();
    map.controls[google.maps.ControlPosition.TOP_RIGHT].push(searchDiv);
    map.addListener('click', function () {
        infoWindow.close();
        searchDiv.className = '';
    });
    searchDiv.onclick = function () {
        searchDiv.className = 'open';
    };
    searchInput.onkeyup = function(){
        if (event.keyCode === 13) {
            initQuery(searchInput.value);
        }
    };
};

function initData(response) {
    if (map) {
        initMarkers(response.data);
    } else {
        setTimeout(function () {
            initData(response);
        }, 2000);
    }
}

function clearMarkers() {
    let marker;
    while (marker = markers.pop()) {
        marker.setMap(null);
    }
}

/**
 * @param {JobItem[]} jobItems
 */
function initMarkers(jobItems) {
    clearMarkers();

    let jobItem;
    while (jobItem = jobItems.pop()) {
        if (jobItem.location && jobItem.coords) {
            initMarker(jobItem);
        }
    }
}

/**
 * @param {JobItem} jobItem
 *
 * @return {google.maps.Symbol}
 */
function getMarkerIcon(jobItem) {
    const makeIcon = function (fill, stroke) {
            return '/marker.php'
                + '?fill=' + encodeURIComponent(fill)
                + '&stroke=' + encodeURIComponent(stroke);
        },
        icons = {
            'icon': makeIcon('#EE4444', '#450c0c'),
            'icon-salary': makeIcon('#88BB55', '#3d572b'),
            'icon-remote': makeIcon('#bbba55', '#595928'),
            'icon-visa': makeIcon('#bb6a95', '#5c3b50')
        };
    let type = ['icon'];

    if (jobItem.salary) {
        type.push('salary');
    }

    if (jobItem.remote) {
        type.push('remote');
    }

    if (jobItem.paidRelocation) {
        type.push('relocation');
    }

    if (jobItem.visaSponsor) {
        type.push('visa');
    }

    // other types go here

    type = type.sort().join('-');

    if (!icons[type]) {
        console.warn('No marker icon for ' + type + ' (job ' + jobItem.guid + ')', jobItem);

        return icons['icon'];
    }

    return icons[type];
}

/**
 * @param {JobItem} jobItem
 */
function initMarker(jobItem) {
    const marker = new google.maps.Marker({
        position: jobItem.coords,
        title: jobItem.title,
        icon: getMarkerIcon(jobItem),
        map: map
    });
    marker.addListener('click', function () {
        infoWindow.setContent([
            '<div class="iw-title">',
                '<a target="_blank" href="' + jobItem.link + '">' + jobItem.title + '</a>',
                ' at ',
                '<a target="_blank" href="https://www.google.com/search?q=' + encodeURIComponent(jobItem.company) + '">' + jobItem.company + '</a>',
            '</div>',
            '<div class="iw-subtitle">',
            '  <div class="iw-date">' + jobItem.pubDate + '</div>',
            '  <div class="iw-tags">',
            '    <span class="iw-tag money">'+(jobItem.salary || '$???')+'</span>',
            '    <span class="iw-tag">' + jobItem.categories.join('</span><span class="iw-tag">') + '</span>',
            '  </div>',
            '</div>',
            '<div class="iw-content">' + jobItem.description + '</div>'
        ].join(''));

        infoWindow.open(map, marker);
    });
    markers.push(marker);
}

function setProgress(percent) {
    if (percent === 0) {
        progressDiv.style.transition = 'none';
        progressDiv.style.width = '0';
    } else {
        progressDiv.style.transition = 'width 1s';
        progressDiv.style.width = Math.max(Math.min(percent, 100), 0).toFixed(2) + '%';
    }
}

function initQuery(search) {
    clearMarkers();
    setTimeout(function () {
        let total = 1, curr = 0;

        setProgress(0);
        jsonpipe.flow(
            '/?action=markers&search=' + encodeURIComponent(search),
            {
                'delimiter': '\n\n',
                'onHeaders': function (statusText, headers) {
                    total = (headers['x-total-count'] * 1) || 1;
                },
                'success': function (data) {
                    curr++;
                    initMarker(data);
                    setProgress(curr / total * 100.00);
                },
                'error': function (errorMsg) {
                    console.error('Error reading chunk: ' + errorMsg);
                },
                'complete': function () {
                    setProgress(100);
                },
                'timeout': 1000 * 60 * 60,
                'method': 'GET',
                'headers': {},
                'data': ''
            }
        );
    }, 5);
}

initQuery('');
