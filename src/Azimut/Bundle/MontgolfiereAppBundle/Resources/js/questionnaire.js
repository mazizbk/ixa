
var montgolfiereGradientCanvas;
var montgolfiereKnob = {
    getXYFromValue: function getXYFromValue(value, offsetx, offsety, knob) {
        offsety -= 5; // Main arcs are offseted by 5px
        var angleRad = knob.angle(value);
        return {
            x: knob.xy - Math.cos(angleRad) * (knob.radius - offsetx),
            y: knob.xy - Math.sin(angleRad) * (knob.radius - offsety)
        };
    },
    triangle: function triangle(context, pos1, pos2, pos3, color) {
        context.beginPath();
        context.fillStyle = color;
        context.moveTo(pos1.x, pos1.y);
        context.lineTo(pos2.x, pos2.y);
        context.lineTo(pos3.x, pos3.y);
        context.fill();
    },
    canvasColor: function canvasColor(knob) {
        var context;
        if (!montgolfiereGradientCanvas) {
            montgolfiereGradientCanvas = document.createElement('canvas');
            montgolfiereGradientCanvas.width = knob.w * knob.scale + 1;
            montgolfiereGradientCanvas.height = knob.h * knob.scale + 1;
            context = montgolfiereGradientCanvas.getContext('2d');
            context.fillStyle = montgolfiereKnob.makeGradient(context, knob.scale, knob.w);
            context.fillRect(0, 0, montgolfiereGradientCanvas.width, montgolfiereGradientCanvas.height);
        }
        else {
            context = montgolfiereGradientCanvas.getContext('2d');
        }

        var point = montgolfiereKnob.getXYFromValue(knob.cv, 0, 0, knob);
        var color = context.getImageData(point.x, point.y, 1, 1);

        return color.data;
    },
    makeGradient: function makeGradient(context, scale, w) {
        var gradient = context.createLinearGradient(w * scale, 0, 0, 0);
        gradient.addColorStop(0.000, 'rgba(255, 126, 0, 1.000)');
        gradient.addColorStop(0.200, 'rgba(250, 218, 67, 1.000)');
        gradient.addColorStop(0.360, 'rgba(148, 216, 16, 1.000)');
        gradient.addColorStop(0.570, 'rgba(16, 216, 138, 1.000)');
        gradient.addColorStop(0.740, 'rgba(17, 203, 131, 1.000)');
        gradient.addColorStop(0.900, 'rgba(13, 204, 211, 1.000)');
        gradient.addColorStop(1.000, 'rgba(152, 205, 254, 1.000)');

        return gradient;
    },
    ngColors: function ngColors() {
        // Update these in CampaignAnalyser::getTrendColors too
        return [
            '#28285a',
            '#374b7d',
            '#5578aa',
            '#5a91af',
            '#7dafb9',
            '#96beaf',
            '#5cb76F',
            '#4BB55C',
            '#3ca54b',
            '#6AC968',
            '#9be188',
            '#fff58c',
            '#fadc7d',
            '#fab95f',
            '#fa9650',
            '#d77346',
            '#eb6446',
        ];
    },
    draw: function () {
        if (this.$.data('skin') === 'montgolfiere' || this.$.data('skin') === 'montgolfiere-ng') {
            var isNg = this.$.data('skin') === 'montgolfiere-ng';

            var mainArcWidth = this.w*50/500;
            this.cursorExt = 100 / mainArcWidth;
            this.g.lineWidth = mainArcWidth * this.scale;

            this.g.beginPath();
            this.g.fillStyle = "#E4E4E4";
            this.g.arc(this.xy, this.xy, (this.h - 7) * this.scale, 0, Math.PI, true);
            this.g.fill();
            // this.g.beginPath();
            // this.g.fillStyle = "#FFFFFF";
            // this.g.arc(this.xy, this.xy, (this.h - 7) * this.scale, 0, Math.PI, true);
            // this.g.fill();
            this.g.beginPath();
            this.g.fillStyle = "#F1F1F1";
            this.g.arc(this.xy, this.xy, (this.h - 10) * this.scale, 0, Math.PI, true);
            this.g.fill();

            if (!this.radiusReduced) {
                this.radius -= 10 * this.scale;
                this.radiusReduced = true;
            }

            var color;
            if(!isNg) {
                color = montgolfiereKnob.canvasColor(this);
                this.g.strokeStyle = 'rgba('+color[0]+', '+color[1]+', '+color[2]+', '+color[3]+')';

                // Draw the arc
                this.g.beginPath();
                this.g.arc(this.xy, this.xy - 4, this.radius, Math.PI, Math.PI*2);
                this.g.stroke();
            }
            else {
                var colors = montgolfiereKnob.ngColors();
                var pc = (this.cv - this.o.min) * 100 / (this.o.max - this.o.min);
                color = colors[Math.floor(pc * (colors.length-1) / 100)];
                color = color.substr(1).match(/.{1,2}/g).map(function(s){return parseInt(s, 16);}); // Color is currently one string in base 16, convert to 3 strings in base 10
                color.push(255); // Opacity

                var colorRadius = Math.PI / colors.length; // Each color has 1/17th of a half-circle
                var spacerRadius = Math.PI/180*.5; //.5deg

                for(var i = 0; i < colors.length; i++) {
                    this.g.beginPath();
                    this.g.strokeStyle = colors[i];
                    this.g.arc(this.xy, this.xy - 4, this.radius, Math.PI+(colorRadius*i)+spacerRadius, Math.PI+(colorRadius*(i+1))-spacerRadius);
                    this.g.stroke();
                }
            }

            var circleRadius = (this.g.lineWidth / 2) + 2.5 * this.scale;
            var circlePosition = montgolfiereKnob.getXYFromValue(this.cv, 0, 0, this);

            // Shadow circle, offsetted by 5px
            this.g.beginPath();
            this.g.strokeStyle = "rgba(87, 87, 87, 0.33)";
            this.g.lineWidth = 5;
            this.g.arc(circlePosition.x + 2.5 * this.scale, circlePosition.y + 2.5 * this.scale, circleRadius, 0, 2 * Math.PI);
            this.g.stroke();

            // White outer circle (stroke) + white semi-transparent inner (fill)
            this.g.beginPath();
            this.g.fillStyle = "#FFFFFFBB";
            this.g.strokeStyle = "#FFF";
            this.g.lineWidth = 5;
            this.g.arc(circlePosition.x, circlePosition.y, circleRadius - this.w*2.5/500 * this.scale, 0, 2 * Math.PI);
            this.g.fill();
            this.g.stroke();
            // Grey circle inside white circle
            this.g.beginPath();
            this.g.strokeStyle = "rgba(0, 0, 0, 0.44)";
            this.g.lineWidth = 1;
            this.g.arc(circlePosition.x, circlePosition.y, circleRadius - this.w*5/500 * this.scale, 0, 2 * Math.PI);
            this.g.stroke();

            // Triangles
            var tr11 = montgolfiereKnob.getXYFromValue(this.cv - 20, 0, 0, this);
            var tr12 = montgolfiereKnob.getXYFromValue(this.cv - 3, 8 * this.scale, 8 * this.scale, this);
            var tr13 = montgolfiereKnob.getXYFromValue(this.cv - 3, -8 * this.scale, -8 * this.scale, this);
            montgolfiereKnob.triangle(this.g, tr11, tr12, tr13, "#575757");
            var tr21 = montgolfiereKnob.getXYFromValue(this.cv + 20, 0, 0, this);
            var tr22 = montgolfiereKnob.getXYFromValue(this.cv + 3, 8 * this.scale, 8 * this.scale, this);
            var tr23 = montgolfiereKnob.getXYFromValue(this.cv + 3, -8 * this.scale, -8 * this.scale, this);
            montgolfiereKnob.triangle(this.g, tr21, tr22, tr23, "#575757");

            // Thermometer
            var thermWidth = this.w*95/500;
            var thermHeight = thermWidth*31.5/35;
            var thermometer = this.$.parents('.knob-container').find('svg');
            var thermOffset = this.h*50/250;
            thermometer
                .attr('width', thermWidth)
                .attr('height', thermHeight)
                .css('position', 'absolute')
                .css('top', '50%')
                .css('left', 'calc(50% - '+thermWidth+'px / 2)')
            ;
            thermometer.find('path').attr('style', 'fill: rgba('+color[0]+', '+color[1]+', '+color[2]+', '+color[3]+')');
            if(!isNg) {
                thermometer.find('style').remove(); // Remove color transition
            }

            this.g.beginPath();
            this.g.fillStyle = 'rgba(154, 154, 154, 0.1)';
            this.g.scale(1, .045);
            this.g.shadowBlur = 10;
            this.g.shadowColor = "#000000";
            this.g.arc(this.w*this.scale/2, ((this.h - thermOffset/2) * this.scale)/0.045, 50*this.scale, 0, Math.PI*2);
            this.g.fill();
            this.g.scale(1, 1/0.045);

            if(!this.hasRendered) {
                this.$c.attr('tabindex', 1);
                this.$c.on('keydown keyup', (ev) => {
                    this.$.trigger(ev);
                });
                this.hasRendered = true;
            }

            return false;
        }
    }
};

module.exports = montgolfiereKnob;
