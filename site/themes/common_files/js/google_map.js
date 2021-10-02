/**
 * Created by 3-DEVs_FARIHA on 24-Mar-16.
 */

    var __processMapRequest = function(options){
        var ths = $(this);
        ths.address = [];
        ths.all_items = [];
        ths.marker_locations = [];
        ths.locations=[];
        //ths.bounds = new google.maps.LatLngBoudns();
        ths.geocoder = new google.maps.Geocoder();
        ths.infowindow = new google.maps.InfoWindow();
        ths.mapContainer = options.mapContainer;
        ths.clientAreas = options.clientAreas;

        ths.map = new google.maps.Map(document.getElementById(ths.mapContainer), {
            zoom: 9,
            center: new google.maps.LatLng(23.7458, 90.3947),
            mapTypeId: google.maps.MapTypeId.ROADMAP
            });

        for(var i in ths.clientAreas){
            var this_loc = '', this_loc_title = '';
            if(ths.clientAreas[i]['client_area'] !== undefined){
                this_loc = ths.clientAreas[i]['client_area']+','+ths.clientAreas[i]['client_zone']+','+ths.clientAreas[i]['client_city'];
                this_loc_title = i;
                }
            else if(ths.clientAreas[i]['company_area'] !== undefined ){
                this_loc = ths.clientAreas[i]['company_area']+','+ ths.clientAreas[i]['company_zone']+','+ths.clientAreas[i]['company_city'];
                this_loc_title = i;
                }

            ths.address.push([this_loc_title, this_loc]);
            ths.marker_locations.push([this_loc]);

            }

        ths.init = function(){

             ths.getLocTitles(ths.marker_locations);

            if(ths.marker_locations.length)
                ths.getLngLat(0);

            };

        ths.displayMarkerOnMap = function(){
            for(var i in ths.locations){
                ths.position = new google.maps.LatLng(ths.locations[i][1], ths.locations[i][2]);
                ths.marker = new google.maps.Marker({
                    position: ths.position,
                    map: ths.map

                    });

                    google.maps.event.addListener(ths.marker,'click',(function(marker, i){
                        return function(){
                            ths.infowindow.setContent(ths.all_items[i]);
                            ths.infowindow.open(ths.map, marker);
                        }
                    })(ths.marker,i));


                }
            };

        ths.getLngLat = function(i){
            if(ths.marker_locations.length > i){

                setTimeout(function() {
                    ths.geocoder.geocode({'address': ths.marker_locations[i][0]}, function (results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            ths.marker_locations[i][1] = results[0].geometry.location.lat();
                            ths.marker_locations[i][2] = results[0].geometry.location.lng();
                        }

                        ths.getLngLat(++i);
                    });
                },1000);
                ths.displayMarkerOnMap();

                }
            else
                ths.displayMarkerOnMap();


            return false;
            };

        ths.getLocTitles = function(locations){
            var k;
            var temp = '';
            var p = '' ;
            var m=0;
            var q= '';
            var locs = [];
            var n = 0;

            if(ths.address.length==ths.marker_locations.length){

                while(m <= ths.marker_locations.length-1){

                    p = ths.marker_locations[m][0];
                    var n = locs.filter(function(value){
                        //clog(value);
                        return value == p;
                    }).length;
                    if(n<=0) {

                        q = ths.marker_locations[m][0];
                        }


                    temp = '<div class="text-primary text-bg text-bold">'+ths.address[m][0]+'</div>'+'<div>'+ths.marker_locations[m][0]+'</div>'


                    for(var j=m+1 ; j<ths.marker_locations.length;j++){
                        k=j+1;

                        if(k>ths.marker_locations.length) break;

                        if(p==ths.marker_locations[j][0]){


                            temp = temp +' '+ '<div class="text-primary text-bg text-bold">'+ths.address[j][0]+'</div>'+'<div>'+ths.marker_locations[j][0]+'</div>';



                        }

                    }

                    ths.all_items.push(temp);


                    var l = locs.filter(function(value){
                        return value == q;
                    }).length;

                    if(l>=1) {
                        ths.all_items.pop();
                    }
                    else if(l<=0)
                        locs.push(q);
                    ++m;


                    }

                }

            for(var t=0;t<ths.marker_locations.length;++t){
                for(var r=0;r<locs.length;++r){
                    if(ths.marker_locations[t][0] == locs[r]){

                        var s = ths.locations.filter(function(value){

                            return value[0] == locs[r];
                        }).length;

                        if(s>=1) {

                            continue;

                            }
                        else{
                            ths.locations.push(ths.marker_locations[t]);
                            }

                        }
                    }
                }

        };


        /*ths.getMarkers = function(locations){
            var k;
            var temp = '';
            var p ;
            var m=0;

            if(ths.address.length==ths.marker_locations.length){

                while(m <= ths.marker_locations.length-1){

                    p = ths.marker_locations[m][0];

                    //temp = '<div class="text-primary text-bg text-bold">'+ths.address[m][0]+'</div>'+'<div>'+ths.marker_locations[m][0]+'</div>'
                    //clog('m');
                    //clog(m);

                    for(var j=m+1 ; j<ths.marker_locations.length;j++){
                        k=j+1;
                       // clog('j');
                       // clog(j);
                        if(k>ths.marker_locations.length) break;

                        if(p==ths.marker_locations[j][0]){

                            p = p + ' ' + ths.marker_locations[j][0];

                         //   temp = temp +' '+ '<div class="text-primary text-bg text-bold">'+ths.address[j][0]+'</div>'+'<div>'+ths.marker_locations[j][0]+'</div>';

                         //   clog(locations);
                            locations.splice(j,1);


                        }

                    }
                   // clog(ths.all_items);
                   // ths.all_items.push(temp);

                    var l = ths.locations.filter(function(value){
                        return value[0] == p;
                    }).length;

                    if(l>=2) {
                        ths.all_items.pop();
                        }
                        ++m;
                    //clog(locations);

                    }

                }
            return locations;
            };*/
        ths.init();

        return ths;
        };



/*function get_loc_titles(marker_locations,address){
    var k;
    var temp = '';
    var p ;
    var m=0;
    var locations=[];

    while(m <= marker_locations.length-1){

        p = marker_locations[m][0];
        temp = '<div class="text-primary text-bg text-bold">'+address[m][0]+'</div>'+'<div>'+marker_locations[m][0]+'</div>'
        for(var j=m+1 ; j<marker_locations.length;++j){
            k=j+1;
            if(k>marker_locations.length) break;
            if(p==marker_locations[j][0]){

                p = p + ' ' + marker_locations[j][0];
                temp = temp +' '+ '<div class="text-primary text-bg text-bold">'+address[j][0]+'</div>'+'<div>'+marker_locations[j][0]+'</div>';
                marker_locations.splice(j,1);
            }
        }
        locations.push(temp);
        var l = marker_locations.filter(function(value){
            return value[0] == p;
        }).length;

        if(l>=2) {

            locations.pop();
        }

        ++m;
    }


    return locations;
}*/

/*
function get_loc_titles(allMarkers){

    var k;
    var temp = '';
    var p ;
    var m=0;
    var locations=[];

    while(m <= allMarkers.length-1){

        p = allMarkers[m]['param'];
        temp = '<div class="text-primary text-bg text-bold">'+allMarkers[m]['title']+'</div>'+'<div>'+allMarkers[m]['param']+'</div>'
        for(var j=m+1 ; j<allMarkers.length;++j){
            k=j+1;
            if(k>allMarkers.length) break;
            if(p==allMarkers[j]['param']){

                p = p + ' ' + allMarkers[j]['param'];
                temp = temp +' '+ '<div class="text-primary text-bg text-bold">'+allMarkers[j]['title']+'</div>'+'<div>'+allMarkers[j]['param']+'</div>';
               allMarkers.splice(j,1);
                }
            }
        locations.push(temp);
        var l = allMarkers.filter(function(value){
            return value.param == p;
            }).length;

        if(l>=2) {

            locations.pop();
            }

        ++m;
        }


    return locations;
    }*/


function get_lng_lat(marker_locations){
    var geocoder = new google.maps.Geocoder();
   /* var marker_locations = [];
    var address = [];
    for(var i in reply_data){
        var this_loc = '', this_loc_title = '';
        if(reply_data[i]['client_area'] !== undefined){
            this_loc = reply_data[i]['client_area']+','+reply_data[i]['client_city'];
            this_loc_title = i;
        }
        else if(reply_data[i]['company_area'] !== undefined ){
            this_loc = reply_data[i]['company_area']+','+reply_data[i]['company_city'];
            this_loc_title = i;
        }

        address.push([this_loc_title, this_loc]);
        marker_locations.push([this_loc]);
    };*/
    var getLngLat = function(i){
        if(marker_locations.length > i){
            geocoder.geocode( { 'address': marker_locations[i][0]}, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    marker_locations[i][1] = results[0].geometry.location.lat();
                    marker_locations[i][2] = results[0].geometry.location.lng();
                    }
                getLngLat(++i);
                });
            }
        else return marker_locations;

        return false;
        };
    }