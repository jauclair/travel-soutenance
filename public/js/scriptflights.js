// Class
class FlightsMonitor {
    constructor(delai=10000) {
        // Render
        this.delai = delai;       // delai pour le timer
        this.update = false;      // mise à autmatique par le timer
        this.lat = null;          // offset fenêtre OpenStreetMap
        this.lon = null;          // offset fenêtre OpenStreetMap
        this.lonLeft = null;      // Coodonnées GPS de la carte affichée
        this.flightid             // Identifiant du vol
        this.lonRight = null;
        this.latTop = null;
        this.latBottom = null;
        this.latPrev = null;      // Coodonnées GPS de la position précédente
        this.lonPrev = null;
        this.planeToTopPrev = null;     // Coodonnées pixel de la position précédente
        this.planeToLeftPrev = null;
        this.map = $("#map");           // Référence sur l'élément map
        this.plane = $("#plane");       // Référence sur l'élément plane
        this.info = $("#flightInfo");   // Référence sur l'élément flightinfo
        this.title = $("#title");       // Référence sur l'élément title
        this.selectId = $('#selectId'); // Référence sur l'élément select
        this.initValues();              // Initilisation des tailles et positions
        // Opensky
        this.waitapi = false;           // waiting for api server responsese
         // callsign idantifiant du vol
        let idFlight = $('#flightid').data('id');
        if (idFlight.length > 2){
            this.callsign_id = $('#flightid').data('id').trim().toUpperCase();
            this.update = true;
        }
        else
            this.callsign_id = null;
        this.apiUrl = 'https://opensky-network.org/api/states/all'; // URL de l'api opensky
        this.icao24 = 0;           // (string) Unique ICAO 24-bit address of the transponder in hex string representation.
        this.callsign = 1;         // (string) Callsign of the vehicle (8 chars). Can be null if no callsign has been received.
        this.origin_country = 2;   // (string) Country name inferred from the ICAO 24-bit address.
        this.time_position = 3;    // (int) Unix timestamp (seconds) for the last position update. Can be null if no position report was received by OpenSky within the past 15s.
        this.last_contact = 4;     // (int) Unix timestamp (seconds) for the last update in general. This field is updated for any new, valid message received from the transponder.
        this.longitude = 5;        // (float) WGS-84 longitude in decimal degrees. Can be null.
        this.latitude = 6;         // (float) WGS-84 latitude in decimal degrees. Can be null.
        this.baro_altitude = 7;    // (float) Barometric altitude in meters. Can be null.
        this.on_ground = 8;        // (boolean) Boolean value which indicates if the position was retrieved from a surface position report.
        this.velocity = 9;         // (float) Velocity over ground in m/s. Can be null.
        this.true_track = 10;      // (float) True track in decimal degrees clockwise from north (north=0°). Can be null.
        this.vertical_rate = 11;   // (float) Vertical rate in m/s. A positive value indicates that the airplane is climbing, a negative value indicates that it descends. Can be null.
        this.sensors = 12;         // (int[]) IDs of the receivers which contributed to this state vector. Is null if no filtering for sensor was used in the request.
        this.geo_altitude = 13;    // (float) Geometric altitude in meters. Can be null.
        this.squawk = 14;          // (string) The transponder code aka Squawk. Can be null.
        this.spi = 15;             // (boolean) Whether flight status indicates special purpose indicator.
        this.position_source = 16; // (int) Origin of this state’s position: 0 = ADS-B, 1 = ASTERIX, 2 = MLAT
    }

    // Initialise values
    initValues(){
        // Map dimentions
        this.mapWidth = this.map.width();
        this.mapHeight = this.map.height();
        // Map postion
        this.mapLeft = this.map.position().left;
        this.mapTop = this.map.position().top;
        // Info dimentions
        this.infoWidth = this.info.width();
        this.infoHeight = this.info.height();
        // Marges fenêtre OpenStreetMap par rapport au centre
        this.marginX = 2.0;
        this.marginY = (this.mapHeight/this.mapWidth) * this.marginX;
        // Position limite de l'avion qui déclenche le recentrage de la carte
        this.proxiX = this.marginX / 4.0;
        this.proxiY = this.marginY / 4.0;
        // Move plane outside (invisible)
        this.plane.animate({left: -200 + 'px'}, "fast");
        this.plane.animate({top: -200 + 'px'}, "fast");
    }

    // Called on resized event and new flight selected
    resize(newFlight=false) {
        let lat = this.lat;
        let lon = this.lon;
        this.lat = null;
        this.lon = null;
        this.initValues();
        if(newFlight)
            return;
        this.updateMap(lat, lon);
    }

    // Fixe l'id de l'avion à suivre
    setCallsign(callsign) {
        // Arrêt de la mise à jour
        if(callsign === "Stop"){
            this.update = false;
            return;
        }
        this.update = true;
        this.callsign_id = callsign.trim().toUpperCase();
        this.getFlightsInfos();
        this.resize(true);
    }

    // Nétoyage du texte
    escapeHtml(text) {
        // Si 0, null ou false
        if(!text){
            return text;
        }
        text = text.toString();
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // affecte le champs #flightInfo (utilisé pour faire un clear)
    displayMessage(msg){
        $('#flightInfo').html(msg);
    }

    // affecte le champs #flightInfo (ajput un élément)
    appendMessage(msg){
        $('#flightInfo').append(msg);
    }

    // Mise en palce du tableau d'infos
    setupInfos(){
        // Premier appel set position zone d'info
        let infoPosiLeft = this.mapLeft + this.mapWidth - this.infoWidth - 6;
        let infoPosiTop = this.mapTop + 4;
        // Move and display info table
        this.info.animate({left: infoPosiLeft + 'px'}, "fast");
        this.info.animate({top: infoPosiTop + 'px'}, "fast");
        this.info.show();
    }

    // Mise à jour des coordonnées de la carte
    updateMap(lat, lon){
        let first = false;
        let center = false;
        // Premier appel
        if (this.lat == null && this.lon == null){
            first = true;
        }
        // Position de l'avion nécéssite un recentrage
        if (Math.abs(this.lonLeft - lon) < this.proxiX ||
            Math.abs(this.lonRight - lon) < this.proxiX ||
            Math.abs(this.latTop - lat) < this.proxiY ||
            Math.abs(this.latBottom - lat) < this.proxiY
        ){
            center = true;
        }
        // Positionnement de la table html
        if(first)
        {
            // Premier appel set position zone d'info
            this.setupInfos();
        }
        // Centrage de la carte
        if(first || center){
            // Set openstreetmap position and size (defined by window corners)
            this.lonLeft = lon - this.marginX;
            this.lonRight = lon + this.marginX;
            this.latTop = lat + this.marginY;
            this.latBottom = lat - this.marginY;
            this.lat = lat;
            this.lon = lon;
            // Coordinates : left bottom | right top
            let src = `https://www.openstreetmap.org/export/embed.html?bbox=${this.lonLeft}%2C${this.latBottom}%2C${this.lonRight}%2C${this.latTop}&amp;layer=mapnik`;
            this.map[0].src = src;
        }
        // La carte a été reposotionnée
        return first || center;
    }

    // Fonction movePlane
    movePlane(lat, lon){
        // Au premier appel ou en cas de réinitialisation de la carte et on positionne l'avion au centre
        if (this.updateMap(lat, lon)){
            // Plane position
            let planeToLeft = this.mapLeft + this.mapWidth / 2;
            let planeToTop = this.mapTop + this.mapHeight / 2;
            // Move plane
            this.plane.animate({left: planeToLeft + 'px'}, "fast");
            this.plane.animate({top: planeToTop + 'px'}, "fast");
        }
        // Sinon on déplace de l'avion sur la carte
        else {
            // Plane position
            let rapportLon = Math.abs(this.lonLeft - lon) / Math.abs(this.marginX * 2);
            let rapportLat = Math.abs(this.latTop - lat) / Math.abs(this.marginY * 2);
            let planeToLeft = this.mapLeft + this.mapWidth * rapportLon;
            let planeToTop = this.mapTop + this.mapHeight * rapportLat;

            // Plane direction
            let dx = lon - this.lonPrev;
            let dy = lat - this.latPrev;
            let angle = 0;
            if(dx < 0 && dy < 0)
                angle = -Math.atan(dy / dx) * 180 / Math.PI + 180;
            else if(dx < 0 && dy > 0)
                angle = -Math.atan(dy / dx) * 180 / Math.PI - 180;
            else
                angle = -Math.atan(dy / dx) * 180 / Math.PI;
            // Avoid random spinning while near fully ouest direction
            if(angle < -178)
                angle = 180;

            // Move plane to new coordinates step by spep
            if(this.planeToTopPrev !== null && this.planeToLeftPrev !== null){
                // Calc step values (how to reach target from where we are)
                let mx = 1;
                let my = 1;
                if(planeToLeft < this.planeToLeftPrev)
                    mx = -1;
                if(planeToTop < this.planeToTopPrev)
                    my = -1;
                // Start coordinates
                let x = this.planeToLeftPrev;
                let y = this.planeToTopPrev;
                // Move plane
                while(true){
                    // Calc remaining distance to target
                    let diffX = Math.abs(x - planeToLeft);
                    let diffY = Math.abs(y - planeToTop);
                    // If both latitude and longitude taget were reache end loop
                    if (diffX < 1 && diffY < 1)
                        break;
                    // If longitude target not reached one more step
                    if (diffX > 1){
                        this.plane.animate({left: x + 'px'}, "fast");
                        x+=mx;
                    }
                    // If latitude target not reached one more step
                    if (diffY > 1){
                        this.plane.animate({top: y + 'px'}, "fast");
                        y+=my;
                    }
                }
            }
            // Move plane directly to new coordinates
            /*
            this.plane.animate({left: planeToLeft + 'px'}, "fast");
            this.plane.animate({top: planeToTop + 'px'}, "fast");
            */
            this.planeToTopPrev = planeToTop;
            this.planeToLeftPrev = planeToLeft;
            // Set plane direction
            if(!isNaN(angle))    {
                this.plane.animate(
                    { deg: angle },
                    {
                        duration: 600,
                        step: function(now) {
                            $(this).css({ transform: 'rotate(' + now + 'deg)' });
                        }
                    }
                );


            }
        }
        // Mise à jour des oodonnées GPS de la position précédente
        this.latPrev = lat;
        this.lonPrev = lon;
    }

    // Appel de la fonction ajax de jQuery
    getFlightsInfos(){
        let self = this;
        if (self.waitapi)
            return false;
        $.ajax({
            type: 'GET',
            url: self.apiUrl,
            dataType: 'json',
            success: function(data){
                if(!$.isEmptyObject(data)){
                    self.displayMessage('');
                    self.appendMessage('<table id="flightInfoTable" class="table">');
                    let found = false;
                    let callsign;
                    let count = 0;
                    $.each(data.states, function (index, flight) {
                        callsign = flight[self.callsign].trim().toUpperCase();
                        if(count++ < 200 && callsign != "")
                            self.selectId.append(`<option value="${callsign}">${callsign}</option>`);
                        if (callsign == self.callsign_id){
                            self.appendMessage('<tr><td class="tdl">Icao24</td><td class="tdl">' + self.escapeHtml(flight[self.icao24]) + '</td></tr>');
                            self.appendMessage('<tr class="bld"><td class="tdl">Callsign</td><td class="tdl">' + self.escapeHtml(flight[self.callsign]) + '</td></tr>');
                            self.appendMessage('<tr class="bld"><td class="tdl">Origin country</td><td class="tdl">' + self.escapeHtml(flight[self.origin_country]) + '</td></tr>');
                            self.appendMessage('<tr><td class="tdl">Time position</td><td class="tdl">' + self.escapeHtml(flight[self.time_position]) + '</td></tr>');
                            self.appendMessage('<tr><td class="tdl">Last contact</td><td class="tdl">' + self.escapeHtml(flight[self.last_contact]) + '</td></tr>');
                            self.appendMessage('<tr class="bld"><td class="tdl">Longitude</td><td class="tdl">' + self.escapeHtml(flight[self.longitude]) + '</td></tr>');
                            self.appendMessage('<tr class="bld"><td class="tdl">Latitude</td><td class="tdl">' + self.escapeHtml(flight[self.latitude]) + '</td></tr>');
                            self.appendMessage('<tr><td class="tdl">Baro altitude</td><td class="tdl">' + self.escapeHtml(flight[self.baro_altitude]) + '</td></tr>');
                            self.appendMessage('<tr class="bld"><td class="tdl">On ground</td><td class="tdl">' + self.escapeHtml(flight[self.on_ground]) + '</td></tr>');
                            self.appendMessage('<tr><td class="tdl">Velocity</td><td class="tdl">' + self.escapeHtml(flight[self.velocity]) + '</td></tr>');
                            self.appendMessage('<tr><td class="tdl">True track</td><td class="tdl"' + self.escapeHtml(flight[self.true_track]) + '</td></tr>');
                            self.appendMessage('<tr><td class="tdl">Vertical rate</td><td class="tdl">' + self.escapeHtml(flight[self.vertical_rate]) + '</td></tr>');
                            self.appendMessage('<tr><td class="tdl">Sensors</td><td class="tdl">' + self.escapeHtml(flight[self.sensors]) + '</td></tr>');
                            self.appendMessage('<tr><td class="tdl">Geo altitude</td><td class="tdl">' + self.escapeHtml(flight[self.geo_altitude]) + '</td></tr>');
                            self.appendMessage('<tr><td class="tdl">Squawk</td><td class="tdl">' + self.escapeHtml(flight[self.squawk]) + '</td></tr>');
                            self.appendMessage('<tr><td class="tdl">Spi</td><td class="tdl">' + self.escapeHtml(flight[self.spi]) + '</td></tr>');
                            self.appendMessage('<tr><td class="tdl">Position source</td><td class="tdl">' + self.escapeHtml(flight[self.position_source]) + '</td></tr>');
                            found = true;
                        }
                        if(found){
                            self.selectId.append("<option value='Stop'>Stop</option>");
                            self.title.html("Vol en cours : " + self.escapeHtml(flight[self.callsign]));
                            self.movePlane(flight[self.latitude], flight[self.longitude]);
                            self.appendMessage('</table>');
                            return false;
                        }
                    });
                    if(!found){
                        self.selectId.append("<option value='Stop'>Stop</option>");
                        self.setupInfos();
                        self.title.html("Aucun vol en cours");
                        self.appendMessage('<tr><td>Vol : ' + self.escapeHtml(self.callsign_id) + ' non trouvé</td></tr>');
                        self.appendMessage('</table>');
                    }
                }
                else
                  console.log('Data is empty');
            },
            // Code éxécuté avant la requête ajax
            beforeSend: function(){
                self.waitapi = true;
                $('#waitdiv').show();
            },
            // Code éxécuté après la requête ajax
            complete: function(){
                $('#waitdiv').hide();
                self.waitapi = false;
                // Arrêt de l'appel automatique de la requête ajax par timer pour mise à jour réguliére des cordonnées de l'avion
                if (typeof self.update !== "undefined" && self.update == false)
                    return;
                // Appel automatique de la requête ajax par timer pour mise à jour réguliére des cordonnées de l'avion
                setTimeout(self.getFlightsInfos, self.delai);
            },
            error: function(e){
                if(e.statusText !==  "OK")
                    console.log(e.statusText);
            },
            timeout: 50000
        });
    }
}

// Instanciation de l'objet FlightsMonitor
const flight = new FlightsMonitor(4000);
flight.getFlightsInfos();

// Appelé réguliérement par le timer
function getFlightsInfos(){
    flight.getFlightsInfos();
}

// Appeler sur click du bouton (arrête l'appel régulier par le timer)
function stopUpdate(){
    flight.stopUpdate();
}

// Appeler sur resize, remet a jour les position et les dimentions
function resizedEvent(){
    flight.resize();
}

// Appeler sur selection d'un vol
function setCallsign(callsign){
    flight.setCallsign(callsign);
}

// Resize event listener
$(document).ready(function(){
    $(window).resize(function(){
        resizedEvent();
    });
});

// Select on change listener
$('#selectId').on('change', function () {
    let selectVal = $("#selectId option:selected").val();
    setCallsign(selectVal);
});
