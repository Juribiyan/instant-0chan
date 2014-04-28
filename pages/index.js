var loops = [ 
    { url: 'anonymous_0chan.ogg' },
    { url: 'aphex_twin_windowlicker.ogg' },
    { url: 'better_off_alone.ogg' },
    { url: 'binarpilot_goof.ogg' },
    { url: 'chrissu_rawfull_panorama.ogg' },
    { url: 'crystal_castles_crimeware.ogg' },
    { url: 'culine_die_for_you.ogg' },
    { url: 'daft_punk_around_the_world.ogg' },
    { url: 'eiffel_65_im_blue.ogg' },
    { url: 'london_elektricity_had_a_little_fight.ogg' },
    { url: 'massive_attack_paradise_circus_zeds_dead_rmx.ogg' },
    { url: 'mata_beach_sand.ogg' },
    { url: 'metrick_your_world.ogg' },
    { url: 'modified_motion_1up.ogg' },
    { url: 'monsta_holdin_on.ogg' },
    { url: 'paradise_cracked.ogg' },
    { url: 'paramyth_cowbell_rock.ogg' },
    { url: 'parove_stelar_the_phantom.ogg' },
    { url: 'scatman_john_ima_scatman.ogg' },
    { url: 'sigur_ros_saeglopur.ogg' },
    { url: 'the_laziest_men_on_mars_all_your_base_are_belong_to_us.ogg' },
    { url: 'the_prodigy_omen.ogg' },
    { url: 'underworld_cowgirl.ogg' } 
];

var urlprefix = "../pages/loops/";

var Grid = {
    genGrid: function(h, v, pattern) {
        if(typeof pattern === 'undefined' || !pattern.length || pattern === 'void') pattern = [0];
        else { if(pattern === 'lit' || pattern == 1) pattern = [9]; 
        else { if(pattern === 'dim' || pattern == 1) pattern = [1]; } }
        pattern = pattern.toString().replace(/[^\da-f]/gmi, '');
        if(!pattern.length) pattern = '0';
        var html = '';
        var p = 0, x = 0, y = 0;
        for( ; y < v; y++) {
            for( ; x < h; x++) {
                if(p >= pattern.length) p = 0;
                html += '<div class="a-cell cell '+this.getClass(pattern[p])+'" '
                    +'id="cell_'+(y+1)+'_'+(x+1)+'" '
                    +'data-color="'+pattern[p]+'"></div>';
                    
                p++;
            }
            if(y !== (v-1)) html+='<br />';
            x = 0;
        }
        return html;
    },
    colorCodes: ['void', 'green', 'yellow', 'orange', 'blue', 'crimson', 'violet', 'mono'],
    getClass: function(num) {
        if(parseInt(num, 16) > 15) return 'void';
        var cl = '';
        if(parseInt(num, 16)/8 >= 1) cl = 'lit '+this.colorCodes[parseInt(num, 16)-8];
        else cl = 'dim '+this.colorCodes[parseInt(num, 16)];
        return cl;
    },
    allColorCodes: function() {
        return this.colorCodes.join(' ') + ' dim lit';
    },
    derivePattern: function(scope) {
        var pattern = '';
        if(typeof scope === 'undefined') scope = '*';
        $(scope).find('.a-cell').each(function() {
            pattern += $(this).data('color');
        });
        return pattern;
    },
    getPrimaryColor: function(scope) {
        if(typeof scope === 'undefined') scope = '*';
        var weights = {};
        var pattern = this.derivePattern(scope).split('');
        iter(pattern, function(letter) {
            var color = Grid.getClass(letter).replace(/(lit|dim|( ))+/,'');
            if(typeof weights[color] === 'undefined') weights[color] = 0;
            weights[color]++;
        });
        var maxWeight = 0, primary = 'void';
        iter_obj(weights, function(key, value) {
            if(key !== 'void' && value >= maxWeight) {
                primary = key;
                maxWeight = value;
            }
        });
        return this.barColors[primary];
    },
    countActiveCells: function(scope) {
        if(typeof scope === 'undefined') scope = '*';
        var count = 0;
        var pattern = this.derivePattern(scope).split('');
        iter(pattern, function(letter) {
            var color = Grid.getClass(letter).replace(/(lit|dim|( ))+/,'');
            if(color !== 'void') count++;
        });
        return count;
    },
    barColors: {
        green: '#59c44d',
        yellow: '#cccf41',
        orange: '#d2993e',
        blue: '#49b6c7',
        crimson: '#cc4368',
        violet: '#8238d8',
        mono: '#d2d2d2',
        void: '#d2d2d2'
    }
};

var pattern = defaultPattern;

var patternBuffer = pattern;

var brushcolor = false, brushcode = false, drawContext = {};

var isMouseDown = false;
$(document).ready(function() {
    $('body').mousedown(function() {
        isMouseDown = true;
    })
    .mouseup(function() {
        isMouseDown = false;
    });
    $('#nullgrid').click(function() {
        // window.location.href = "http://0chan.ru";
        if(!audio.disabled) {
            audio.stop();
            audio.loadLoop(1);
        }
    });
    $('.brush').click(function() {
        $('.brush').removeClass('selected'); $(this).addClass('selected');
        brushcolor = Grid.getClass($(this).data('color'));
        brushcode = $(this).data('color');
    });

    $('#closePalette').click(function() {
        $('#playSwitcher').show();
        effect = true;
        brushcolor = false;
        $('#boards').slideDown();
        $('#palette').slideUp();
        $('#nullgrid').removeClass('edit-mode');
        pattern = {
            x: $('#nullgrid').data('size').x,
            y: $('#nullgrid').data('size').y,
            pattern: Grid.derivePattern('#nullgrid')
        };
        bars.trimmingConstant = Math.floor(Math.sqrt(Grid.countActiveCells()));
        localStorage.setItem('customPattern', JSON.stringify(pattern));
    });
    $('#resampler').submit(function(event) {
        event.preventDefault();
        $('#nullgrid').resample( {
            x: $('#width').val(),
            y: $('#height').val(),
            pattern: $('#pattern').val()
        });
    });
    $('#getPattern').click(function() {
        $('#pattern').val(Grid.derivePattern('#nullgrid'));
        var size = $('#nullgrid').data('size');
        $('#width').val(size.x);
        $('#height').val(size.y);
    });
    $('#clearGrid').click(function() {
        $('#nullgrid').resample({pattern: '0'});
    });
    $('#reset').click(function() {
        $('#nullgrid').resample(defaultPattern);
    });
        
    $('#nullgrid').resample(pattern)
    .on("mouseenter", ".a-cell", function() { 
        if(isMouseDown && $(this).hasClass("a-cell") && brushcolor) {
            $(this).paint();
        }
        $(this).flash();
    })
    .on("mousedown", ".a-cell", function() {
        if(brushcolor) {
            $(this).paint();
        }
        $(this).flash();
    });
    
    var canvas = $('#bars');
    drawContext = canvas[0].getContext('2d');
    drawContext.fillStyle = Grid.getPrimaryColor();

    $('body').on('click', 'center a', function() {
        $.get($(this).attr('href'), function(res) { 
            var buf = $('<div></div>').html(res).find('#boards').html(); 
            $('#boards').html(buf);
        });
        return false;
    });
});

function editmode() {
    $('#nullgrid').off('click');
    effect = false;
    $('#palette').slideDown();
    $('#boards').slideUp();
    $('#nullgrid').addClass('edit-mode');
    $('#playSwitcher').hide();
}

var fade = {in: 0, out: 300, await: 0};
var effect = true;

(function( $ ) {
    $.fn.paint = function() {
        this.removeClass(Grid.allColorCodes()).addClass(brushcolor).data('color', brushcode);
        drawContext.fillStyle = Grid.getPrimaryColor();
        return this;
    };
    $.fn.flash = function() {
        if(effect === false || !this.length || this.hasClass('busy')) return;
        var self = this;
        self.addClass('busy');
        var color = Grid.getClass(self.data('color'));
        var popup = $('#pop'+self.attr('id'));
        if(!popup.length) {
            popup =  $("<div></div>").addClass("cell xpop").attr('id', 'pop'+self.attr('id'))
            .css({ display: 'none',
                position: 'absolute',
                left: self.position().left,
                top: self.position().top
            })
            .appendTo('#nullgrid');
        }
        popup.addClass(color+"-hov-shadow "+color+"-hov").fadeIn(fade.in, function() {
            setTimeout(function () {
                popup.removeClass(color+"-hov-shadow "+color+"-hov").fadeOut(fade.out, function() {
                    self.removeClass('busy');
                });
            }, fade.await);
        });
    };
    $.fn.resample = function(xyp) {
        var x = typeof xyp.x !== 'undefined' ? xyp.x : this.data('size').x;
        var y = typeof xyp.y !== 'undefined' ? xyp.y : this.data('size').y;
        this.html(Grid.genGrid(x, y, xyp.pattern)).data('size', {x: x, y: y});
        resampleLayers();
        drawContext.fillStyle = Grid.getPrimaryColor();
        bars.trimmingConstant = Math.floor(Math.sqrt(Grid.countActiveCells()));
        return this;
    }
}( jQuery ));

var audio = {
    buffer: {},
    compatibility: {},
    supported: true,
    source_loop: {},
    source_once: {},
    disabled: true
};

var currentLoop = {};
audio.loadLoop = function(immed, n) {
    audio.disabled = true;
    var newLoop = {};
    if(typeof n === 'number' && n < loops.length && n >= 0) {
        newLoop = loops[n];
        if (audio.source_loop._playing) {
            audio.source_loop[audio.compatibility.stop](0);
            audio.source_loop._playing = false;
            audio.source_loop._startTime = 0;
            if (audio.compatibility.start === 'noteOn') {
                audio.source_once[audio.compatibility.stop](0);
            }
        } 
    }
    else {
        var reduced = (loops.length == 1) ? loops : loops.filter(function(e){return e!==currentLoop});
        newLoop = reduced[Math.floor(Math.random() * reduced.length)];
    }
    var req = new XMLHttpRequest();
    req.open('GET', urlprefix+newLoop.url, true);
    $('#playSwitcher').text('Loading...').off();
    req.responseType = 'arraybuffer';
    
    req.onload = function() {
        $('#playSwitcher').text('вкл/выкл музыку').click(audio.play);
        if(newLoop.url === 'eiffel_65_im_blue.ogg') 
            $('#nullgrid').resample(imBlue);
        else $('#nullgrid').resample(defaultPattern);
        audio.context.decodeAudioData(
            req.response,
            function(buffer) {
                if(newLoop.hasOwnProperty('treshold')) {
                    bars.treshold = newLoop.treshold;
                }
                else {
                    bars.treshold = bars.defaultTreshold;
                }
                audio.buffer = buffer;
                audio.source_loop = {};
                currentLoop  = newLoop;
                audio.disabled = false;
                if(immed) audio.play();
            }
        );
    };
    req.send();
}

var imBlue = { pattern: 
    '00cccccccc00'+
    '0cccccccccc0'+
    'cc4444444ccc'+
    'cc444444cccc'+
    'cc44444cc4cc'+
    'cc44444cc4cc'+
    'cc4444cc44cc'+
    'cc4444cc44cc'+
    'cc444cc444cc'+
    'cc444cc444cc'+
    'cc44cc4444cc'+
    'cc44cc4444cc'+
    'cc4cc44444cc'+
    'cc4cc44444cc'+
    'cccc444444cc'+
    'ccc4444444cc'+
    '0cccccccccc0'+
    '00cccccccc00', x: 12, y: 18 }; 
// defaultPattern = imBlue;

try {
    window.AudioContext = window.AudioContext || window.webkitAudioContext;
    audio.context = new window.AudioContext();
} catch(e) {
    audio.supported = false;
}

var visualizer = {};

if(audio.supported) {
    (function() {
        var start = 'start',
            stop = 'stop',
            buffer = audio.context.createBufferSource();
     
        if (typeof buffer.start !== 'function') {
            start = 'noteOn';
        }
        audio.compatibility.start = start;
     
        if (typeof buffer.stop !== 'function') {
            stop = 'noteOff';
        }
        audio.compatibility.stop = stop;
    })();
    
    audio.loadLoop(false);
    
    visualizer = new VisualizerSample();
}

audio.stop = function() {
    if(audio.disabled) return;
    audio.source_loop[audio.compatibility.stop](0);
    audio.source_loop._playing = false;
    audio.source_loop._startTime = 0;
    if (audio.compatibility.start === 'noteOn') {
        audio.source_once[audio.compatibility.stop](0);
    }
}

audio.play = function() {
    if(audio.disabled) return;
    if (audio.source_loop._playing) {
        audio.source_loop[audio.compatibility.stop](0);
        audio.source_loop._playing = false;
        audio.source_loop._startTime = 0;
        if (audio.compatibility.start === 'noteOn') {
            audio.source_once[audio.compatibility.stop](0);
        }
        $('.as-hidable').fadeOut(1000);
        audio.loadLoop(false);
    } 
    else {
        audio.source_loop = audio.context.createBufferSource();
        audio.source_loop.buffer = audio.buffer;
        audio.source_loop.loop = true;
        audio.source_loop.connect(visualizer.analyser);
 
        if (audio.compatibility.start === 'noteOn') {
            audio.source_once = audio.context.createBufferSource();
            audio.source_once.buffer = audio.buffer;
            audio.source_once.connect(visualizer.analyser);
            audio.source_once.noteGrainOn(0, 0, audio.buffer.duration);

            audio.source_loop[audio.compatibility.start](audio.buffer.duration);
        } else {
            audio.source_loop[audio.compatibility.start](0, 0);
        }
        audio.source_loop._playing = true;

        frame(visualizer.draw.bind(visualizer));
        
        $('.audiostuff').fadeIn(1000);
    }
    return false;
};

function resampleLayers() {
    $('.audiostuff').css({'top': bars.offsetTop+'px'});
    $('#shadow').width(bars.shadowWidth);
}

var frame = (function() {
return  window.requestAnimationFrame || 
	window.webkitRequestAnimationFrame || 
	window.mozRequestAnimationFrame    || 
	window.oRequestAnimationFrame      || 
	window.msRequestAnimationFrame     || 
	function( callback ) {
		window.setTimeout(callback, 1000 / 60);
	};
})();

var bars = {
	width: 850,
	height: 240,
	smoothing: 0.9,
	fft_size: 2048,
	treshold: 0.65,
    defaultTreshold: 0.65,
	trimmingConstant: 12,
	get offsetTop() {
        return ($('#nullgrid').height() +  70) - this.height;
	},
	get shadowWidth() {
        return $('#nullgrid').width() + 20;
	}
};

function VisualizerSample() {
	this.analyser = audio.context.createAnalyser();

	this.analyser.connect(audio.context.destination);
	this.analyser.minDecibels = -100;
	this.analyser.maxDecibels = 0;

	this.times = new Uint8Array(this.analyser.frequencyBinCount);
}

var normalizer = 0;

VisualizerSample.prototype.draw = function() {
	this.analyser.smoothingTimeConstant = bars.smoothing;
	this.analyser.fftSize = bars.fft_size;
	this.analyser.getByteTimeDomainData(this.times);
	
    drawContext.clearRect(0,0,bars.width,bars.height);
    
        for (var i = 0; i < this.analyser.frequencyBinCount; i++) {
		var value = this.times[i];
		var percent = value / 256;
        
        if(percent > (bars.treshold - normalizer) && !(i % bars.trimmingConstant)) {
            $($('.a-cell')[ i + Math.floor(Math.random()*bars.trimmingConstant) ]).flash();
        }
		var height = bars.height * percent;
		var y = Math.ceil(bars.height - height);
        drawContext.fillRect(i*4, y, 2, height);
	}
	if (audio.source_loop._playing) {
		frame(this.draw.bind(this));
	}
}

VisualizerSample.prototype.getFrequencyValue = function(freq) {
  var nyquist = audio.context.sampleRate/2;
  var index = Math.round(freq/nyquist * this.freqs.length);
  return this.freqs[index];
}

function iter(array, callback) {
    if(typeof array !== 'object') return callback(array);
    var i=0, len = array.length;
    for ( ; i < len ; i++ ) {
        callback(array[i]);
    }
}

function iter_obj(object, callback) {
    for (var property in object) {
        if (object.hasOwnProperty(property)) {
            callback(property, object[property]);
        }
    }
}

var cloud20 = {
    pixels: {min: 16, max: 100},
    order: 'name',  displaytype: 'cloud',
    normalize: function(boards) {
        if(typeof boards === 'undefined') boards = this.allboards;
        var ceiling = 0;
        iter(boards, function(board) {
            if(board.postcount > ceiling) ceiling = board.postcount;
        });
        var res = [];
        iter(boards, function(board) {
            res.push({
                name: board.name,
                desc: board.desc,
                postcount: board.postcount,
                pix: Math.round(((cloud20.pixels.max - cloud20.pixels.min)/ceiling)*board.postcount + cloud20.pixels.min)
            });
        });
        this.display(res);
    },
    filter: function(query) {
        var res = [];
        if(query == '') res = this.allboards;
        else {
            query = query.toLowerCase();
            iter(this.allboards, function(board) {
                if(board.name.toLowerCase().search(query) !== -1 || board.desc.toLowerCase().search(query) !== -1)
                    res.push(board);
            });
        }
        this.normalize(res);
    },
    getboards: function() {
        $.getJSON('../boards20.json', function(results) {
            cloud20.allboards = results;
            cloud20.normalize();
        })
    },
    display: function(list) {
        var newhtml = '';
        if(this.order == 'postcount') {
            list.sort(function(a, b) {
                return b.postcount - a.postcount;
            });
        }
        iter(list, function(item) {
            item.nolodash = item.name.split('_')[1];
            newhtml += ('<a class="link20" href="../'+item.name+'/" title="'+ item.desc +'"');
            if(cloud20.displaytype === 'cloud') {
                $('#data20').addClass('cloud-view').removeClass('list-view');
                newhtml += (' style="font-size: '+item.pix+'px"');
            }
            newhtml += '>'+item.nolodash;
            if(cloud20.displaytype === 'list') {
                $('#data20').removeClass('cloud-view').addClass('list-view');
                newhtml += (' — '+ item.desc +'</a> <span class="postcount20">('+ item.postcount +')</span><br />');
            }
            else {
                newhtml += '</a> ';
            }
        });
        $('#data20').html(newhtml);
    }
}