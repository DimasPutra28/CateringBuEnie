<html>

<head>
    <title>Bootstrap Table Expandable - Example</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" />
    <script src="http://code.jquery.com/jquery-2.1.4.min.js" type="text/javascript"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>

    <!-- INCLUDES -->
    <link rel="stylesheet" href="../css/bootstrap-table-expandable.css" />
    <script src="../js/bootstrap-table-expandable.js"></script>
</head>

<body>
    <style>
        table.table-expandable>tbody>tr:nth-child(odd) {
            cursor: pointer;
        }

        table.table-expandable.table-hover>tbody>tr:nth-child(even):hover td {
            background-color: white;
        }

        table.table-expandable>tbody>tr div.table-expandable-arrow {
            background: transparent url(../images/arrows.png) no-repeat scroll 0px -16px;
            width: 16px;
            height: 16px;
            display: block;
        }

        table.table-expandable>tbody>tr div.table-expandable-arrow.up {
            background-position: 0px 0px;
        }

        table.table-sticky-header th {
            color: #fff;
            background-color: #555;
        }
    </style>
    <div class="container">
        <h1>Bootstrap Table Expandable - Example</h1>
        <p>
            Based on
            <a href="http://www.jankoatwarpspeed.com/expand-table-rows-with-jquery-jexpand-plugin/">jExpand</a>
        </p>
        <table class="table table-hover table-expandable table-sticky-header">
            <thead>
                <tr>
                    <th>Country</th>
                    <th>Population</th>
                    <th>Area</th>
                    <th>Official languages</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>United States of America</td>
                    <td>306,939,000</td>
                    <td>9,826,630 km2</td>
                    <td>English</td>
                </tr>
                <tr>
                    <td colspan="5">
                        <h4>Additional information</h4>
                        <ul>
                            <li>
                                <a href="http://en.wikipedia.org/wiki/Usa">USA on Wikipedia</a>
                            </li>
                            <li>
                                <a href="http://nationalatlas.gov/">National Atlas of the United States</a>
                            </li>
                            <li>
                                <a href="http://www.nationalcenter.org/HistoricalDocuments.html">Historical Documents</a>
                            </li>
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table>
        <p>By <a href="http://www.wfcreations.com.br">wfcreations</a></p>
    </div>

    <script>
        (function($) {
            "use strict";

            var name = "stickyTableHeaders",
                id = 0,
                defaults = {
                    fixedOffset: 0,
                    leftOffset: 0,
                    marginTop: 0,
                    scrollableArea: window,
                };

            function Plugin(el, options) {
                // To avoid scope issues, use 'base' instead of 'this'
                // to reference this class from internal events and functions.
                var base = this;

                // Access to jQuery and DOM versions of element
                base.$el = $(el);
                base.el = el;
                base.id = id++;
                base.$window = $(window);
                base.$document = $(document);

                // Listen for destroyed, call teardown
                base.$el.bind("destroyed", $.proxy(base.teardown, base));

                // Cache DOM refs for performance reasons
                base.$clonedHeader = null;
                base.$originalHeader = null;

                // Keep track of state
                base.isSticky = false;
                base.hasBeenSticky = false;
                base.leftOffset = null;
                base.topOffset = null;

                base.init = function() {
                    base.$el.each(function() {
                        var $this = $(this);

                        base.$originalHeader = $("thead:first", this);
                        base.$clonedHeader = base.$originalHeader.clone();
                        $this.trigger("clonedHeader." + name, [base.$clonedHeader]);

                        base.$clonedHeader.addClass("tableFloatingHeader");
                        base.$clonedHeader.css("display", "none");

                        base.$originalHeader.addClass("tableFloatingHeaderOriginal");

                        base.$originalHeader.after(base.$clonedHeader);

                        base.$printStyle = $(
                            '<style type="text/css" media="print">' +
                            ".tableFloatingHeader{display:none !important;}" +
                            ".tableFloatingHeaderOriginal{position:static !important;}" +
                            "</style>"
                        );
                        $("head").append(base.$printStyle);
                    });

                    base.setOptions(options);
                    base.updateWidth();
                    base.toggleHeaders();
                    base.bind();
                };

                base.destroy = function() {
                    base.$el.unbind("destroyed", base.teardown);
                    base.teardown();
                };

                base.teardown = function() {
                    if (base.isSticky) {
                        base.$originalHeader.css("position", "static");
                    }
                    $.removeData(base.el, "plugin_" + name);
                    base.unbind();

                    base.$clonedHeader.remove();
                    base.$originalHeader.removeClass("tableFloatingHeaderOriginal");
                    base.$originalHeader.css("visibility", "visible");
                    base.$printStyle.remove();

                    base.el = null;
                    base.$el = null;
                };

                base.bind = function() {
                    base.$scrollableArea.on("scroll." + name, base.toggleHeaders);
                    if (!base.isWindowScrolling) {
                        base.$window.on(
                            "scroll." + name + base.id,
                            base.setPositionValues
                        );
                        base.$window.on("resize." + name + base.id, base.toggleHeaders);
                    }
                    base.$scrollableArea.on("resize." + name, base.toggleHeaders);
                    base.$scrollableArea.on("resize." + name, base.updateWidth);
                };

                base.unbind = function() {
                    // unbind window events by specifying handle so we don't remove too much
                    base.$scrollableArea.off("." + name, base.toggleHeaders);
                    if (!base.isWindowScrolling) {
                        base.$window.off("." + name + base.id, base.setPositionValues);
                        base.$window.off("." + name + base.id, base.toggleHeaders);
                    }
                    base.$scrollableArea.off("." + name, base.updateWidth);
                };

                base.toggleHeaders = function() {
                    if (base.$el) {
                        base.$el.each(function() {
                            var $this = $(this),
                                newLeft,
                                newTop,
                                newTopOffset = base.isWindowScrolling ?
                                isNaN(base.options.fixedOffset) ?
                                base.options.fixedOffset.outerHeight() :
                                base.options.fixedOffset :
                                base.$scrollableArea.offset().top +
                                (!isNaN(base.options.fixedOffset) ?
                                    base.options.fixedOffset :
                                    0),
                                offset = $this.offset(),
                                header = $this.find("thead"),
                                scrollTop = base.$scrollableArea.scrollTop() + newTopOffset,
                                scrollLeft = base.$scrollableArea.scrollLeft(),
                                scrolledPastTop = base.isWindowScrolling ?
                                scrollTop > offset.top :
                                newTopOffset > offset.top,
                                notScrolledPastBottom =
                                (base.isWindowScrolling ? scrollTop : 0) <
                                offset.top +
                                $this.height() -
                                base.$clonedHeader.height() -
                                (base.isWindowScrolling ? 0 : newTopOffset);

                            if (scrolledPastTop && notScrolledPastBottom) {
                                newLeft = offset.left - scrollLeft + base.options.leftOffset;
                                newTop = -offset.top - header.height() + base.options.marginTop;

                                base.$originalHeader.css({
                                    position: "fixed",
                                    "margin-top": newTop,
                                    left: newLeft,
                                    "z-index": 3, // #18: opacity bug
                                });
                                base.leftOffset = newLeft;
                                base.topOffset = newTop;
                                base.$clonedHeader.css("display", "");
                                if (!base.isSticky) {
                                    base.isSticky = true;
                                    // make sure the width is correct: the user might have resized the browser while in static mode
                                    base.updateWidth();
                                }
                                base.setPositionValues();
                            } else if (base.isSticky) {
                                base.$originalHeader.css("position", "static");
                                base.$clonedHeader.css("display", "none");
                                base.isSticky = false;
                                base.resetWidth(
                                    $("td,th", base.$clonedHeader),
                                    $("td,th", base.$originalHeader)
                                );
                            }
                        });
                    }
                };

                base.setPositionValues = function() {
                    var winScrollTop = base.$window.scrollTop(),
                        winScrollLeft = base.$window.scrollLeft();
                    if (
                        !base.isSticky ||
                        winScrollTop < 0 ||
                        winScrollTop + base.$window.height() > base.$document.height() ||
                        winScrollLeft < 0 ||
                        winScrollLeft + base.$window.width() > base.$document.width()
                    ) {
                        return;
                    }
                    base.$originalHeader.css({
                        top: base.topOffset - (base.isWindowScrolling ? 0 : winScrollTop),
                        left: base.leftOffset - (base.isWindowScrolling ? 0 : winScrollLeft),
                    });
                };

                base.updateWidth = function() {
                    if (!base.isSticky) {
                        return;
                    }
                    // Copy cell widths from clone
                    if (!base.$originalHeaderCells) {
                        base.$originalHeaderCells = $("th,td", base.$originalHeader);
                    }
                    if (!base.$clonedHeaderCells) {
                        base.$clonedHeaderCells = $("th,td", base.$clonedHeader);
                    }
                    var cellWidths = base.getWidth(base.$clonedHeaderCells);
                    base.setWidth(
                        cellWidths,
                        base.$clonedHeaderCells,
                        base.$originalHeaderCells
                    );

                    // Copy row width from whole table
                    base.$originalHeader.css("width", base.$clonedHeader.width());
                };

                base.getWidth = function($clonedHeaders) {
                    var widths = [];
                    $clonedHeaders.each(function(index) {
                        var width,
                            $this = $(this);

                        if ($this.css("box-sizing") === "border-box") {
                            width = $this[0].getBoundingClientRect().width; // #39: border-box bug
                        } else {
                            var $origTh = $("th", base.$originalHeader);
                            if ($origTh.css("border-collapse") === "collapse") {
                                if (window.getComputedStyle) {
                                    width = parseFloat(
                                        window.getComputedStyle(this, null).width
                                    );
                                } else {
                                    // ie8 only
                                    var leftPadding = parseFloat($this.css("padding-left"));
                                    var rightPadding = parseFloat($this.css("padding-right"));
                                    // Needs more investigation - this is assuming constant border around this cell and it's neighbours.
                                    var border = parseFloat($this.css("border-width"));
                                    width =
                                        $this.outerWidth() - leftPadding - rightPadding - border;
                                }
                            } else {
                                width = $this.width();
                            }
                        }

                        widths[index] = width;
                    });
                    return widths;
                };

                base.setWidth = function(widths, $clonedHeaders, $origHeaders) {
                    $clonedHeaders.each(function(index) {
                        var width = widths[index];
                        $origHeaders.eq(index).css({
                            "min-width": width,
                            "max-width": width,
                        });
                    });
                };

                base.resetWidth = function($clonedHeaders, $origHeaders) {
                    $clonedHeaders.each(function(index) {
                        var $this = $(this);
                        $origHeaders.eq(index).css({
                            "min-width": $this.css("min-width"),
                            "max-width": $this.css("max-width"),
                        });
                    });
                };

                base.setOptions = function(options) {
                    base.options = $.extend({}, defaults, options);
                    base.$scrollableArea = $(base.options.scrollableArea);
                    base.isWindowScrolling = base.$scrollableArea[0] === window;
                };

                base.updateOptions = function(options) {
                    base.setOptions(options);
                    // scrollableArea might have changed
                    base.unbind();
                    base.bind();
                    base.updateWidth();
                    base.toggleHeaders();
                };

                // Run initializer
                base.init();
            }

            // A plugin wrapper around the constructor,
            // preventing against multiple instantiations
            $.fn[name] = function(options) {
                return this.each(function() {
                    var instance = $.data(this, "plugin_" + name);
                    if (instance) {
                        if (typeof options === "string") {
                            instance[options].apply(instance);
                        } else {
                            instance.updateOptions(options);
                        }
                    } else if (options !== "destroy") {
                        $.data(this, "plugin_" + name, new Plugin(this, options));
                    }
                });
            };

            $(function() {
                $(".table-expandable").each(function() {
                    var table = $(this);
                    table.children("thead").children("tr").append("<th></th>");
                    table.children("tbody").children("tr").filter(":odd").hide();
                    table
                        .children("tbody")
                        .children("tr")
                        .filter(":even")
                        .click(function() {
                            var element = $(this);
                            element.next("tr").toggle("slow");
                            element.find(".table-expandable-arrow").toggleClass("up");
                        });
                    table
                        .children("tbody")
                        .children("tr")
                        .filter(":even")
                        .each(function() {
                            var element = $(this);
                            element.append(
                                '<td><div class="table-expandable-arrow"></div></td>'
                            );
                        });
                });

                $(".table-sticky-header").each(function() {
                    var table = $(this);
                    $(table).stickyTableHeaders();
                });
            });
        })(jQuery);
    </script>
</body>

</html>