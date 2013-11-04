function tooltip() {
    xOffset = 10;
    yOffset = 20;
    jQuery(".tooltip").hover(function (a) {
        this.t = jQuery(this).attr("name");
        jQuery("body").append("<p id='tooltip'>" + this.t + "</p>");
        jQuery("#tooltip").css("top", (a.pageY - xOffset) + "px").css("left", (a.pageX + yOffset) + "px").fadeIn("fast")
    }, function () {
        jQuery(this).attr("name", this.t);
        jQuery("#tooltip").remove()
    });
    jQuery(".tooltip").mousemove(function (a) {
        jQuery("#tooltip").css("top", (a.pageY - xOffset) + "px").css("left", (a.pageX + yOffset) + "px")
    })
}

function tooltipLeft() {
    xOffset = 10;
    yOffset = 20;
    jQuery(".tooltipLeft").hover(function (a) {
        this.t = jQuery(this).attr("name");
        jQuery("body").append("<p id='tooltip'>" + this.t + "</p>");
        jQuery("#tooltip").css("width", "250px");
        jQuery("#tooltip").css("top", (a.pageY - xOffset) + "px").css("left", (a.pageX - 265 - yOffset) + "px").fadeIn("fast")
    }, function () {
        jQuery(this).attr("name", this.t);
        jQuery("#tooltip").remove()
    });
    jQuery(".tooltipLeft").mousemove(function (a) {
        jQuery("#tooltip").css("top", (a.pageY - xOffset) + "px").css("left", (a.pageX - 265 - yOffset) + "px")
    })
}

function toggleContainer(c, b) {
    c = jQuery("#" + c);
    var a = "#" + b;
    b = jQuery(a);
    if (c.css("display") == "" || c.css("display") == "block") {
        c.css("display", "none");
        b.attr("src", "/images/right-arr.gif")
    } else {
        c.css("display", "block");
        b.attr("src", "/images/down-arr.gif")
    }
}

function redirectToUrl(c, d, b) {
    var a = c + d;
    if (b) {
        a += b
    }
    window.location = a
}

function closeFacebox() {
    jQuery(document).trigger("close.facebox")
}

function lauchFaceboxIframe(url)
{
	jQuery.facebox({ iframe: url });
}

function doRate(a, b) {
    jQuery("#rating_container_" + a).load("/rate/" + a + "/" + b)
}

function checkIt(b, a) {
    var c = document.getElementsByName(b);
    for (i = 0; i < c.length; i++) {
        c[i].checked = a
    }
}

function toggleTerminationDate(b) {
    var a = b.value.split(":");
    if (a[1] == 1) {
        jQuery("#termination_date").css("display", "block")
    } else {
        jQuery("#termination_date").css("display", "none")
    }
}

function clickToPrint(a) {
    docPrint = window.open("", "Print");
    docPrint.document.open();
    docPrint.document.write("<html><head>");
    docPrint.document.write('<title>Wikipixel</title></head><body onLoad="self.print()" style="padding:10px;">');
    docPrint.document.write(jQuery("#print_" + a).html());
    docPrint.document.write("</body></html>");
    docPrint.document.close();
    docPrint.focus();
    jQuery.post("/file/Logprint", {
        id: a
    })
}
jQuery(document).ready(function () {
    jQuery(".group-grid").bind("contextmenu", function (a) {
        jQuery(".rightClick").hide();
        var b = jQuery(this).attr("id");
        jQuery("#rightClickOnGroup_" + b).show();
        jQuery("#rightClickOnGroup_" + b).css("top", a.pageY);
        jQuery("#rightClickOnGroup_" + b).css("left", a.pageX);
        return false
    });
    jQuery(".folder-div").bind("contextmenu", function (a) {
        jQuery(".rightClick").hide();
        var b = jQuery(this).attr("id");
        jQuery("#rightClickOnFolder_" + b).show();
        jQuery("#rightClickOnFolder_" + b).css("top", a.pageY);
        jQuery("#rightClickOnFolder_" + b).css("left", a.pageX);
        return false
    });
    jQuery(".file-div").bind("contextmenu", function (a) {
        jQuery(".rightClick").hide();
        var b = jQuery(this).attr("id");
        jQuery("#rightClickOnFile_" + b).show();
        jQuery("#rightClickOnFile_" + b).css("top", a.pageY);
        jQuery("#rightClickOnFile_" + b).css("left", a.pageX);
        return false
    });
    jQuery("html").click(function (a) {
        jQuery(".rightClick").hide()
    });
    jQuery(".rightClick").click(function (a) {
        jQuery(".rightClick").hide()
    });
    jQuery(".rightClick ul a").click(function (a) {
        jQuery(".rightClick").hide()
    });
    jQuery(".rightClick ul li.download").mouseover(function () {
        var a = jQuery(this).attr("rel");
        jQuery(this).addClass("hover");
        jQuery("#" + a).css("left", jQuery(this).width() + "px");
        jQuery("#" + a).css("width", jQuery("#" + a).width() + 20 + "px");
        jQuery("#" + a).show()
    }).mouseout(function () {
        var a = jQuery(this).attr("rel");
        jQuery(this).removeClass("hover");
        jQuery("#" + a).css("width", jQuery("#" + a).width() - 20 + "px");
        jQuery("#" + a).hide()
    });
    jQuery(".rightClick ul li.manage").mouseover(function () {
        var a = jQuery(this).attr("rel");
        jQuery(this).addClass("hover");
        jQuery("#" + a).css("left", jQuery(this).width() + "px");
        jQuery("#" + a).css("width", jQuery("#" + a).width() + 20 + "px");
        jQuery("#" + a).show()
    }).mouseout(function () {
        var a = jQuery(this).attr("rel");
        jQuery(this).removeClass("hover");
        jQuery("#" + a).css("width", jQuery("#" + a).width() - 20 + "px");
        jQuery("#" + a).hide()
    });
    jQuery("#action-buttons .buttons").mouseover(function () {
        var a = jQuery.browser;
        jQuery(this).addClass("hover");
        jQuery(".jcrop-holder div div .jcrop-tracker").css("z-index", 0);
        jQuery(".jcrop-holder div div .jcrop-tracker").parent().css("z-index", 0);
        jQuery(".jcrop-holder div div .jcrop-tracker").parent().parent().css("z-index", 0)
    }).mouseout(function (b) {
        if (b.relatedTarget != null) {
            var a = jQuery.browser;
            jQuery(this).removeClass("hover");
            jQuery(".jcrop-holder div div .jcrop-tracker").css("z-index", 660);
            jQuery(".jcrop-holder div div .jcrop-tracker").parent().css("z-index", 610);
            jQuery(".jcrop-holder div div .jcrop-tracker").parent().parent().css("z-index", 600)
        }
    })
});

function unescapeHTML(a) {
    return jQuery("<div/>").html(a).text()
}

function urldecode(a) {
    return decodeURIComponent((a + "").replace(/\+/g, "%20"))
}

function detectPlugins(b, a) {
    if (navigator.mimeTypes.length > 0 && navigator.mimeTypes[b] != undefined) {
        return navigator.mimeTypes[b].enabledPlugin != null
    } else {
        if (window.ActiveXObject) {
            try {
                new ActiveXObject(a);
                return true
            } catch (c) {
                return false
            }
        } else {
            return false
        }
    }
}

function getFlashVersion() {
    try {
        try {
            var a = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");
            try {
                a.AllowScriptAccess = "always"
            } catch (b) {
                return "6,0,0"
            }
        } catch (b) {}
        return new ActiveXObject("ShockwaveFlash.ShockwaveFlash").GetVariable("$version").replace(/\D+/g, ",").match(/^,?(.+),?$/)[1]
    } catch (b) {
        try {
            if (navigator.mimeTypes["application/x-shockwave-flash"].enabledPlugin) {
                return (navigator.plugins["Shockwave Flash 2.0"] || navigator.plugins["Shockwave Flash"]).description.replace(/\D+/g, ",").match(/^,?(.+),?$/)[1]
            }
        } catch (b) {}
    }
    return "0,0,0"
}

function detectFlash() {
    var b = detectPlugins("application/x-shockwave-flash", "ShockwaveFlash.ShockwaveFlash");
    if (b) {
        var a = getFlashVersion().split(",").shift();
        return (a > 9)
    }
    return false
}

function serialize(c) {
    var g = function (q) {
        var o = 0,
            n = 0,
            m = q.length,
            p = "";
        for (n = 0; n < m; n++) {
            p = q.charCodeAt(n);
            if (p < 128) {
                o += 1
            } else {
                if (p < 2048) {
                    o += 2
                } else {
                    o += 3
                }
            }
        }
        return o
    };
    var j = function (q) {
        var p = typeof q,
            m;
        var o;
        if (p === "object" && !q) {
            return "null"
        }
        if (p === "object") {
            if (!q.constructor) {
                return "object"
            }
            var l = q.constructor.toString();
            m = l.match(/(\w+)\(/);
            if (m) {
                l = m[1].toLowerCase()
            }
            var n = ["boolean", "number", "string", "array"];
            for (o in n) {
                if (l == n[o]) {
                    p = n[o];
                    break
                }
            }
        }
        return p
    };
    var e = j(c);
    var a, b = "";
    switch (e) {
    case "function":
        a = "";
        break;
    case "boolean":
        a = "b:" + (c ? "1" : "0");
        break;
    case "number":
        a = (Math.round(c) == c ? "i" : "d") + ":" + c;
        break;
    case "string":
        a = "s:" + g(c) + ':"' + c + '"';
        break;
    case "array":
    case "object":
        a = "a";
        var d = 0;
        var f = "";
        var k;
        var h;
        for (h in c) {
            if (c.hasOwnProperty(h)) {
                b = j(c[h]);
                if (b === "function") {
                    continue
                }
                k = (h.match(/^[0-9]+$/) ? parseInt(h, 10) : h);
                f += this.serialize(k) + this.serialize(c[h]);
                d++
            }
        }
        a += ":" + d + ":{" + f + "}";
        break;
    case "undefined":
    default:
        a = "N";
        break
    }
    if (e !== "object" && e !== "array") {
        a += ";"
    }
    return a
}
/*
Array.prototype.inArrayFacebox = function (a) {
    var b = this.length;
    for (var c = 0; c < b; c++) {
        var d = new RegExp(this[c], "g");
        if (d.test(a)) {
            return true
        }
    }
    return false
};*/

function inArray(d, a) {
    var b = d.length;
    for (var c = 0; c < b; c++) {
        if (d[c] == a) {
            return true
        }
    }
    return false
}

function strip_tags(a, c) {
    c = (((c || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join("");
    var b = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
        d = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
    return a.replace(d, "").replace(b, function (f, e) {
        return c.indexOf("<" + e.toLowerCase() + ">") > -1 ? f : ""
    })
}

function faceboxSizes(b, a) {
    this.id = b;
    this.pages = a
}

function attachFiles(b, a) {
    jQuery.post(configPath + "file/attachFiles", {
        from: b,
        to: a
    }, function (d) {
        if (d > 0) {
            var c = jQuery("<div class='success box'>" + __("These two files were linked successfully.") + "</div>");
            jQuery("body").append(c);
            jQuery(c).fadeIn().delay(3000).fadeOut(400, function () {
                jQuery(c).remove()
            })
        }
    })
}

function click2call(c, b, a) {
    jQuery.get("https://ssl.keyyo.com/makecall.html?ACCOUNT=" + c + "&CALLEE=" + b + "&CALLEE_NAME=" + a)
}
var BrowserDetect = {
    init: function () {
        this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
        this.version = this.searchVersion(navigator.userAgent) || this.searchVersion(navigator.appVersion) || "an unknown version";
        this.OS = this.searchString(this.dataOS) || "an unknown OS"
    },
    searchString: function (d) {
        for (var a = 0; a < d.length; a++) {
            var b = d[a].string;
            var c = d[a].prop;
            this.versionSearchString = d[a].versionSearch || d[a].identity;
            if (b) {
                if (b.indexOf(d[a].subString) != -1) {
                    return d[a].identity
                }
            } else {
                if (c) {
                    return d[a].identity
                }
            }
        }
    },
    searchVersion: function (b) {
        var a = b.indexOf(this.versionSearchString);
        if (a == -1) {
            return
        }
        return parseFloat(b.substring(a + this.versionSearchString.length + 1))
    },
    dataBrowser: [{
        string: navigator.userAgent,
        subString: "Chrome",
        identity: "Chrome"
    }, {
        string: navigator.userAgent,
        subString: "OmniWeb",
        versionSearch: "OmniWeb/",
        identity: "OmniWeb"
    }, {
        string: navigator.vendor,
        subString: "Apple",
        identity: "Safari",
        versionSearch: "Version"
    }, {
        prop: window.opera,
        identity: "Opera",
        versionSearch: "Version"
    }, {
        string: navigator.vendor,
        subString: "iCab",
        identity: "iCab"
    }, {
        string: navigator.vendor,
        subString: "KDE",
        identity: "Konqueror"
    }, {
        string: navigator.userAgent,
        subString: "Firefox",
        identity: "Firefox"
    }, {
        string: navigator.vendor,
        subString: "Camino",
        identity: "Camino"
    }, {
        string: navigator.userAgent,
        subString: "Netscape",
        identity: "Netscape"
    }, {
        string: navigator.userAgent,
        subString: "MSIE",
        identity: "Explorer",
        versionSearch: "MSIE"
    }, {
        string: navigator.userAgent,
        subString: "Gecko",
        identity: "Mozilla",
        versionSearch: "rv"
    }, {
        string: navigator.userAgent,
        subString: "Mozilla",
        identity: "Netscape",
        versionSearch: "Mozilla"
    }],
    dataOS: [{
        string: navigator.platform,
        subString: "Win",
        identity: "Windows"
    }, {
        string: navigator.platform,
        subString: "Mac",
        identity: "Mac"
    }, {
        string: navigator.userAgent,
        subString: "iPhone",
        identity: "iPhone/iPod"
    }, {
        string: navigator.platform,
        subString: "Linux",
        identity: "Linux"
    }]
};
BrowserDetect.init();