MappedRepairEvents.Map = function(objects, type, isWidget, customCenterCoordinates, customZoomLevel, customMarkerSrc, customFoundMarkerSrc) {
    
    this.objectType = 'Workshop'; // default
    
    this.objects = objects;
    
    if (customCenterCoordinates && customCenterCoordinates.x && customCenterCoordinates.y) {
        this.customCenterCoordinates = customCenterCoordinates;
    }
    
    this.customZoomLevel = customZoomLevel || 0;
    
    this.pruneCluster = new PruneClusterForLeaflet(100);
    
    if (customMarkerSrc) {
        this.defaultIcon = L.icon({
            iconUrl: customMarkerSrc
        });
    } else {
        this.defaultIcon = L.icon({
            iconUrl: '/img/marker-iconred.png',
            iconRetinaUrl: '/img/marker-iconred@2x.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });
    }
    
    if (customFoundMarkerSrc) {
        this.foundIcon = L.icon({
            iconUrl: customFoundMarkerSrc
        });
    } else {
        this.foundIcon = L.icon({
            iconUrl: '/img/marker-iconpurple.png',
            iconRetinaUrl: '/img/marker-iconpurple@2x.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });
    }

    MappedRepairEvents.MapObject = this;
    
    publicTransportLayer = undefined;
    
    MappedRepairEvents.MapObject.isWidget = isWidget;

    this.blueIcon = L.icon({
        iconUrl: '/img/marker-iconblue.png',
        iconRetinaUrl: '/img/marker-iconblue@2x.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });
    
    if (type == 'search') {
        MappedRepairEvents.MapObject.loadAllWorkshops(null, MappedRepairEvents.MapObject.initSearchMap);
        this.singleMarkerZoom = 11;
    }

    if (type == 'detail') {
        this.singleMarkerZoom = 11;
        MappedRepairEvents.MapObject.initMarkers();
        MappedRepairEvents.MapObject.initDetailMap();
        this.singleMarkerZoom = 15;
    }
    
    if ( this.map && !MappedRepairEvents.MapObject.isWidget ){
        var mapevents = ['moveend', 'dragend'];
        
        for (var i in mapevents ){
            this.map.on(mapevents[i], function() {
                MappedRepairEvents.MapObject.updateCalendar();
            });
        }
        
        MappedRepairEvents.MapObject.reupdate = true;
        
    }
};

MappedRepairEvents.Map.prototype = {
        
    setHeight : function() {
        var newMapHeight = $(window).height() - $('#header').height() - ($('#mapContainer').css('marginTop').replace('px', '') * 2) - 10;
        $('#mapContainer, #map').height(newMapHeight);
        $('#mapContainer, #map').width($('#content .right').width());
        this.map.invalidateSize();
        this.zoomToMarkerLayerBounds();
    },

    setMapAsFixed : function(marginTop) {
        
        $('#mapContainer').scrollToFixed({
            marginTop: marginTop
        });
        
        $(window).on('resize', function() {
            MappedRepairEvents.MapObject.setHeight();
        });
        
        MappedRepairEvents.MapObject.setHeight();
    },
    
    /**
     * extract tags from objects and save them in this.tag.tags
     */
    initTags : function(objects) {
    
        var preparedTags = new Array;
        
        for(var i=0;i<this.objects.length;i++) {
            var tags = this.objects[i].Tags;
            for(var j=0;j<tags.length;j++) {
                preparedTags.push(tags[j]);
            }
        }
          
        this.tag = new MappedRepairEvents.Tag(preparedTags);
        this.tag.tags = this.tag.sortAndMakeTagsUnique(preparedTags);
          
    }

    ,loadAllWorkshops : function(keyword, callback) {
        $.ajax({
            url: '/workshops/ajaxGetAllWorkshopsForMap?keyword=' + keyword,
            async: false,
            success: function(data) {
                MappedRepairEvents.MapObject.allObjects = data.workshops;
                MappedRepairEvents.MapObject.initMarkers();
                if (callback) {
                    callback();
                }
            }
        });
    }
    
    ,loadAllEvents : function(keyword, categories) {
        $.ajax({
            url: '/events/ajaxGetAllEventsForMap?keyword=' + keyword + '&categories=' + categories,
            async: false,
            success: function(data) {
                MappedRepairEvents.MapObject.allObjects = data.events;
                MappedRepairEvents.MapObject.initMarkers();
            }
        });
    }

    ,initDetailMap : function() {
    
        $('#mapContainer a.change-map-size').show();
        var urlForAnalytics = document.location.href.split('/');
          
        $('a.change-map-size').on('click', function() {
            
            if ($('#mapContainer').hasClass('big')) {
                $('#mapContainer').removeClass('big');
                $('#mapContainer, #map').animate({height : '150px', width : '250px'}, 1000, function() {
                    //bei Größenänderung Karte zurechtrücken
                    MappedRepairEvents.MapObject.map.invalidateSize(true);
                    $('div.map-detail .address-wrapper').css('padding-top', '0px');
                    $('div.map-detail h1').css('margin-bottom', '10px');
                    $('div.map-detail #mapContainer').css('margin-left', '0');  // nur für ie7
                    $('div.map-detail .opening-hours').css('margin-top', '0');
                    $('#mapContainer a.change-map-size').html('Karte vergrößern');
            
            
            
                });
            } else {
                $('#mapContainer, #map').animate({height : '538px', width : '990px'}, 1000, function() {
                    //bei Größenänderung Karte zurechtrücken
                    MappedRepairEvents.MapObject.map.invalidateSize(true);
                });
        
        
                $('div.map-detail .address-wrapper').css('padding-top', '575px');
                $('div.map-detail h1').css('margin-bottom', '20px');
                $('div.map-detail #mapContainer').css('margin-left', '-350px'); // nur für ie7
                $('div.map-detail .opening-hours').css('margin-top', '-15px');
        
                $('#mapContainer a.change-map-size').html('Karte verkleinern');
                $('#mapContainer').addClass('big');
            }
        });

    }

    ,initSearchMap : function() {
        
        MappedRepairEvents.MapObject.objects = MappedRepairEvents.MapObject.allObjects;
        
        MappedRepairEvents.MapObject.exampleTextSearchAddress = 'z.B. Berlin oder Postleitzahl';
       
        $('#workshopSearchAddress').val(MappedRepairEvents.MapObject.exampleTextSearchAddress);
        
        $('#workshopSearchAddress').on('focus', function() {
            MappedRepairEvents.Helper.emptyInputField(this, MappedRepairEvents.MapObject.exampleTextSearchAddress);
        });
    
        $('#workshopSearchAddress').on('keyup', function(e) {
            if(e.keyCode == 13) {
                MappedRepairEvents.MapObject.initAddressSearch();
            }
        });        
        $('.box.filter button.submit').on('click', function() {
            MappedRepairEvents.MapObject.initAddressSearch();
        });
        $('.box.filter button.reset').on('click', function() {
            MappedRepairEvents.MapObject.resetSearch();
        });
        $('.box.filter button.reset-widget').on('click', function() {
            MappedRepairEvents.MapObject.resetSearchWidget();
        });
        
        // layout-verbesserung
        $('.categories-dropdown-wrapper label').remove();
        
    }
    
    ,bindMarkers : function(objects, icon) {

        var pmarkers = [];
        
        for(var i=0;i<objects.length;i++) {
            
            var object = objects[i][this.objectType];
            
            // object should always be defined - but its not :-( 
            if (object) {
                
                var bubbleString;
                if (this.objectType == 'Workshop') {
                    bubbleString = this.buildBubbleStringForWorkshops(objects[i]);
                }
                
                if (this.objectType == 'Event') {
                    bubbleString = this.buildBubbleStringForEvents(objects[i]);
                }
                
                var pmarker = new PruneCluster.Marker(object.lat, object.lng, {
                    icon: icon,
                    popup: bubbleString,
                    object: object
                });

                pmarkers.push(pmarker);
                
            }
        }
        
        this.pruneCluster.RegisterMarkers(pmarkers);

        
    }
    
    ,FitFoundBounds : function () {
        var foundMarkers = [];
        var markers = MappedRepairEvents.MapObject.pruneCluster.GetMarkers();
        for(var i in markers) {
            if (markers[i].data.icon.options.iconUrl.match(/purple/)) {
                foundMarkers.push(markers[i]);
            }
        }
        var markersForBoundingBox = foundMarkers;
        if (foundMarkers.length == 0) {
            markersForBoundingBox = markers;
        }
        var bounds = this.pruneCluster.Cluster.ComputeBounds(markersForBoundingBox);
        if (bounds) {
            this.map.fitBounds(new L.LatLngBounds(new L.LatLng(bounds.minLat, bounds.maxLng), new L.LatLng(bounds.maxLat, bounds.minLng)));
        }
    }

    
    ,initMarkers : function() {
        
        this.map = L.map('map').setView([0,0],0);
        
        var tp = 'ls';
        if (L.Browser.retina) {
            tp = 'lr';
        }

        L.tileLayer('https://tile-{s}.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
            attribution: 'Tiles courtesy of <a href="https://hotosm.org/">Humanitarian OSM Team</a>. Map data by <a href="https://openstreetmap.org">OSM</a>',
            maxZoom: 18
        }).addTo(this.map);
        
        this.markerLayer = L.featureGroup();
        this.markerLayer.addTo(this.map);
        
        if (MappedRepairEvents.MapObject.objects 
                && MappedRepairEvents.MapObject.objects.length > 0 
                && (!MappedRepairEvents.MapObject.allObjects || MappedRepairEvents.MapObject.objects.length != MappedRepairEvents.MapObject.allObjects.length)
        ) {
            var icon = this.defaultIcon;
            if (MappedRepairEvents.MapObject.allObjects) {
                icon = this.foundIcon;
            }
            MappedRepairEvents.MapObject.bindMarkers(MappedRepairEvents.MapObject.objects, icon);
        }
        
        if (MappedRepairEvents.MapObject.allObjects) {
            MappedRepairEvents.MapObject.bindMarkers(MappedRepairEvents.MapObject.allObjects, this.defaultIcon);
            // nothing found => show all markers
            if (MappedRepairEvents.MapObject.objects.length == 0 || MappedRepairEvents.MapObject.objects.length == MappedRepairEvents.MapObject.allObjects.length) {
                MappedRepairEvents.MapObject.bindMarkers(MappedRepairEvents.MapObject.objects, this.defaultIcon);
            }
        }
        
        this.markerLayer.addLayer(this.pruneCluster);
        this.FitFoundBounds();
        this.zoomToMarkerLayerBounds();
        
    }
    
    ,zoomToMarkerLayerBounds : function() {
        
        //Zoom to right position and zoomlevel
        if (this.customZoomLevel == 0) {
            if (this.objects.length == 1) {
                this.map.fitBounds(this.boundsForZooming, {'maxZoom': this.singleMarkerZoom});
            } else {
                this.FitFoundBounds();
            }
        } else {
            MappedRepairEvents.MapObject.map.setZoom(this.customZoomLevel);
        }

        this.panToCustomCenterCoordinates();
        
    }
    
    ,panToCustomCenterCoordinates: function() {
        if (this.customCenterCoordinates) {
            this.map.panTo(new L.LatLng(MappedRepairEvents.MapObject.customCenterCoordinates.x, MappedRepairEvents.MapObject.customCenterCoordinates.y));
        }
    }
    
    ,showHomeImageOverlay : function() {
        $('img#mapHomeInfoBox').stop(true).show('slow');
    }

    ,hideHomeImageOverlay : function() {
        $('img#mapHomeInfoBox').stop(true).hide('slow');
    }

    ,buildBubbleStringForWorkshops : function(object) {
        
        // legacy: modify non-cake-object
        if (!object[this.objectType]) {
            object[this.objectType] = object;
        }
        var bubbleString = '<a '+( MappedRepairEvents.MapObject.isWidget ? 'target="_blank" ' : '' )+'href="/' + object.Workshop.url + '">' + object.Workshop.name+'</a><br />';

        if ( object.Events && object.Events.length) {
            bubbleString += this.getWorkshopEventBubbleString(object.Events);
        }
        return bubbleString;
    }
    
    ,buildBubbleStringForEvents : function(object) {
        var bubbleString = '<a href="/' + object.Workshop.url + '">' + object.Workshop.name+'</a><br />';
        bubbleString += this.getWorkshopEventBubbleString(object.Events);
        return bubbleString;
    }

    ,getWorkshopEventBubbleString : function(events) {
        
        var bubbleString = '';
        var mycounter = 1;
        
        for (var i in events) {
            var wevent = events[i];
            
            var d = new Date();
            var date = d.getDate() < 10 ? '0' + d.getDate() : d.getDate();
            var mytoday = d.getFullYear()+'-'+('0' + (d.getMonth()+1)).slice(-2)+'-'+ date;
            
            if ( mytoday < wevent.datumstart_formatted) {
                
                if (mycounter == 1) {
                    bubbleString += '<br /><div><b>Nächste Termine:</b></div>';
                    mycounter = 2;
                }
                
                bubbleString += '<div><a '+( MappedRepairEvents.MapObject.isWidget ? 'target="_blank" ' : '' )+'href="' + wevent.directurl + '#datum">' + MappedRepairEvents.Helper.niceDate(wevent.datumstart_formatted) + ' | ' + MappedRepairEvents.Helper.niceTime(wevent.uhrzeitstart_formatted) + ' Uhr</a></div>';
            }

        }
        return bubbleString;
    }
    
    ,resetSearch : function() {
        
        MappedRepairEvents.MapObject.removeSpinnerFromSearchButton();
        
        $('#workshopSearchAddress').val('');
        $('#workshopSearchAddress').trigger('blur');
        $('.categories-dropdown').val('');
        $('.categories-dropdown-wrapper').hide();
        $('#workshopSearchCategories1').show();
        $('#workshopTags').val('');
        $('#workshopTags').trigger('blur');
        $('#selectedTag').val('');
        MappedRepairEvents.Helper.hideFlashMessage();
        
        MappedRepairEvents.MapObject.initAddressSearch();
        $('.find-events-box').hide();
        
        MappedRepairEvents.MapObject.clearMarkers();
        MappedRepairEvents.MapObject.bindMarkers(MappedRepairEvents.MapObject.allObjects, this.defaultIcon);
        MappedRepairEvents.MapObject.zoomToMarkerLayerBounds();
        
        MappedRepairEvents.Helper.hideAndResetCalendarEventsBox();
        MappedRepairEvents.MapObject.showHomeImageOverlay();

    }
    
    ,resetSearchWidget : function() {
        document.location.reload();
    }
    
    /**
     * die suche nach dem ort benötigt einen geocoding-request => callback!
     */
    ,initAddressSearch : function() {
        var searchString = $.trim($('#workshopSearchAddress').val()); 
        if (searchString != MappedRepairEvents.MapObject.exampleTextSearchAddress && searchString != '') {
            this.applyFilter(searchString);
            this.hideHomeImageOverlay();
        }
    },
    
    addSpinnerToSearchButton : function() {
        MappedRepairEvents.Helper.addSpinner($('#search'));
    },
    
    removeSpinnerFromSearchButton : function() {
        MappedRepairEvents.Helper.removeSpinner($('#search'), 'Suche');
    }
    
    /**
    * addressLatLng is empty if address is not found or not passed
    */
    ,applyFilter : function(sucheOrt) {

        MappedRepairEvents.Helper.quickHideFlashMessage();
        this.clearMarkers();
        
        this.addSpinnerToSearchButton();
        
        $.ajax({
            url: '//nominatim.openstreetmap.org/search?q='+ sucheOrt +'&format=json&viewbox=0,42,25,55&bounded=1&countrycodes=DE,AT,CH',
            async: true,
            success: function(data) {
                
                MappedRepairEvents.MapObject.removeSpinnerFromSearchButton();

                var not_filtered_wrk = [];
                var filtered_wrk = [];

                if (data.length > 0) {
                   
                    var search_point = L.latLng(parseFloat(data[0]['lat']), parseFloat(data[0]['lon']));
               
                    var radiusInKilometer = 15;
                    var radiusFromPoint = radiusInKilometer * 1000;
                   
                    $.each(MappedRepairEvents.MapObject.objects, function(i, object) {
                        var w_lat = parseFloat(object['Workshop']['lat']);
                        var w_lng = parseFloat(object['Workshop']['lng']);
                        var wrk_point = L.latLng(w_lat, w_lng);
                       
                        if (wrk_point.distanceTo(search_point) <= radiusFromPoint) {
                            filtered_wrk.push(object);
                        } else {
                            not_filtered_wrk.push(object);
                        }
                    });
                } else {
                    // search returned no result (city does not exist)
                    not_filtered_wrk = MappedRepairEvents.MapObject.objects;
                }
               
                var filteredObjects = {
                    filtered: filtered_wrk,
                    notfiltered: not_filtered_wrk
                };
               
                var foundMarkers = [];
                var pmarkers = [];
                
                // alle gefundenen marker der map hinzufügen
                if (filteredObjects.filtered) {
                    for(var k=0;k<filteredObjects.filtered.length;k++) {
                        var pmarker = new PruneCluster.Marker(filteredObjects.filtered[k]['Workshop'].lat, filteredObjects.filtered[k]['Workshop'].lng, {
                            icon: MappedRepairEvents.MapObject.foundIcon,
                            popup: MappedRepairEvents.MapObject.buildBubbleStringForWorkshops(filteredObjects.filtered[k]['Workshop']),
                            object: filteredObjects.filtered[k]['Workshop']
                        });
                        pmarkers.push(pmarker);
                    }
                }
                if (filteredObjects.notfiltered) {
                    for(var j=0;j<filteredObjects.notfiltered.length;j++) {
                        var pmarker = new PruneCluster.Marker(filteredObjects.notfiltered[j]['Workshop'].lat, filteredObjects.notfiltered[j]['Workshop'].lng, {
                            icon: MappedRepairEvents.MapObject.defaultIcon,
                            popup: MappedRepairEvents.MapObject.buildBubbleStringForWorkshops(filteredObjects.notfiltered[j]['Workshop']),
                            object: filteredObjects.notfiltered[j]['Workshop']
                        });
                        pmarkers.push(pmarker);
                    }
                }
                
                MappedRepairEvents.MapObject.pruneCluster.RegisterMarkers(pmarkers);
                MappedRepairEvents.MapObject.markerLayer.addLayer(MappedRepairEvents.MapObject.pruneCluster);
                MappedRepairEvents.MapObject.FitFoundBounds();
               
                // Wenn keine mehr vorhanden fehlermeldung anzeigen - ansonsten Kartenausschnitt zurecht setzen
                if (filteredObjects.filtered.length == 0) {
                    if ($('#workshopSearchAddress').val() != '') {
                        MappedRepairEvents.Helper.setFlashMessage('red', 'Mit den aktuellen Filter-Einstellungen wurde keine Initiative gefunden.');
                    }
                } else {
                    /*
                    // (group wichtig, weil auch die nicht gefundenen marker angezeigt werden)
                    var group = new L.featureGroup(foundMarkers);
                    if (filteredObjects.filtered.length == 1) {
                        MappedRepairEvents.MapObject.pruneCluster.FitBounds();
                    } else {
                        // zoomt auf die gefundenen marker
                        MappedRepairEvents.MapObject.map.fitBounds(group.getBounds(), {'padding':[8,8]});
                    }
                    */
                }
               
            }
        });
        
    }
    
    /**
     * löscht alle marker auf der map
     */
    ,clearMarkers : function() {
        this.pruneCluster.RemoveMarkers();
    }
    
    /**
     * @param array objects
     * @param int id
     * @return array objects
     */
    ,doFilterByCategories : function(objects, categoryId) {
        var foundObjects = [];
        for(var i=0;i<objects.length;i++) {
            if ($.inArray(categoryId, objects[i].Workshop.categories) > -1) {
                foundObjects.push(objects[i]);
            }
        }
        return foundObjects;
    }
    
    ,updateCalendar : function() {
        var
            visibleEvents = []
            ,dateChosen = $('#selectedDate').attr('data-date')
            ,mapBounds = MappedRepairEvents.MapObject.map.getBounds()
        ;
        
        var markers = MappedRepairEvents.MapObject.pruneCluster.GetMarkers();
        for(var i in markers) {
            if( mapBounds.contains(markers[i].position) && markers[i].data.object.Events && markers[i].data.object.Events.length ){
                for (var j in markers[i].data.object.Events){
                    visibleEvents.push(markers[i].data.object.Events[j].uid);
                }
            }
        }
        
        $('.calEvent').each(function() {
            
            $(this).hide();
            $(this).removeClass('fc-has-event');
            
            if ($.inArray(parseInt($(this).attr('rel').split(' ')[0]), visibleEvents) == -1) {
                
                $(this).addClass('isntInRadius'); // needed for when elements are traversed again in calendar.ctp
                $(this).removeClass('isInRadius');
                
            } else {
                
                if ($(this).attr('data-date') == dateChosen ) {
                    $(this).show();
                }
                
                $(this).addClass('isInRadius');
                $(this).removeClass('isntInRadius'); // needed for when elements are traversed again in calendar.ctp
            }
        });
        
        $('td.fc-day').each(function() {
            if ( $('.calEvent.isInRadius[data-date='+$(this).attr('data-date')+']').length )  {
                $(this).addClass('fc-has-event');
            } else {
                $(this).removeClass('fc-has-event');
            }
            MappedRepairEvents.Helper.updateDayEventCount($(this));
            
        });
        
        MappedRepairEvents.MapObject.reupdate = false;
    }
    
    ,reupdate : false
};
