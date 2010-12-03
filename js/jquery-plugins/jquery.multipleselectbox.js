/*!
 * jQuery Multiple Select Box Plugin 0.4.8
 * 
 * http://plugins.jquery.com/project/jquerymultipleselectbox
 * http://code.google.com/p/jquerymultipleselectbox/
 * 
 * Apache License 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * @author dreamltf
 * @date 2010/08/28
 * 
 * Depends: jquery.js (v1.3+)
 */
( function($) {
	var PLUGIN_NAMESPACE = "MultipleSelectBox";
	var PLUGIN_STYLE_DISABLED = "disabled";
	var PLUGIN_STYLE_SELECTED = "selected";
	var PLUGIN_STYLE_SELECTING = "selecting";
	var defaultOptions = {
		maxLimit : -1,
		valueRendererArray : null,
		isMouseEventEnabled : true,
		isKeyEventEnabled : true,
		/* callback function */
		onSelectStart : null,
		onSelectEnd : null,
		onSelectChange : null
	};

	/**
	 * Private : Validate MultipleSelectBox
	 * 
	 * @return jQuery
	 */
	function validateMultipleSelectBox() {
		/* yield event handler */
		return $("ul." + PLUGIN_NAMESPACE).yieldMultipleSelectBox().each( function() {
			var $container = $(this);
			var options = $container.data("options");
			var containerInfo = $container.data("info");

			/* trigger callback */
			if ($container.hasClass(PLUGIN_STYLE_SELECTING)) {
				/* reset style */
				$container.removeClass(PLUGIN_STYLE_SELECTING);

				var extraParameters = [$container.serializeMultipleSelectBox()[0], containerInfo.lastStartIndex, containerInfo.lastCurrentIndex, containerInfo.prevStartIndex, containerInfo.prevCurrentIndex];
				if (options.onSelectEnd) {
					$container.trigger("onSelectEnd", extraParameters);
				}
				if (options.onSelectChange && (extraParameters[0] != extraParameters[2] || extraParameters[1] != extraParameters[3])) {
					$container.trigger("onSelectChange", extraParameters);
				}
			}

			/* reset history */
			containerInfo.prevStartIndex = containerInfo.lastStartIndex;
			containerInfo.prevCurrentIndex = containerInfo.lastCurrentIndex;
		});
	};

	/**
	 * Private : Initialize MultipleSelectBox
	 * 
	 * @param $container
	 *            jQuery
	 * @param options
	 *            class
	 * @return jQuery
	 */
	function initializeMultipleSelectBox($container, options) {
		var $rows = $container.getCachedMultipleSelectBoxRows();
		var rowSize = $rows.length;

		/* mouse event */
		if (options.isMouseEventEnabled) {
			$container.bind("mousedown", function(e) {
				var $startRow = $(e.target);
				if (this == $startRow[0]) {
					return true;
				} else if (this != $startRow.parent()[0]) {
					$startRow = $startRow.parents("ul." + PLUGIN_NAMESPACE + ">li").eq(0);
				}

				/* recalculate container and all row's position */
				$container.recalculateMultipleSelectBox(true, true);

				var containerInfo = $container.data("info");
				var startIndex = $startRow.data("index");
				var currentIndex = startIndex;

				/* record */
				var isKeepSelected = false;
				if (options.isKeyEventEnabled) {
					if (e.ctrlKey) {
						isKeepSelected = true;
					}
					if (e.shiftKey) {
						startIndex = containerInfo.lastStartIndex;
					}
				}

				/* reset all style */
				$container.addClass(PLUGIN_STYLE_SELECTING);
				$container.drawMultipleSelectBox(startIndex, currentIndex, true, isKeepSelected, true);

				/* listening */
				$container.bind("mouseenter", function() {
					$(document).unbind("mousemove." + PLUGIN_NAMESPACE);
				}).bind("mouseleave", function() {
					$(document).bind("mousemove." + PLUGIN_NAMESPACE, function(e1) {
						var mouseY = e1.pageY;
						if (mouseY < containerInfo.topPos) {
							if (currentIndex > 0) {
								var targetPos = containerInfo.rowInfoArray[currentIndex].topPos - (containerInfo.topPos - mouseY);
								if (targetPos > 0) {
									for ( var i = currentIndex - 1; i >= 0; i--) {
										if (targetPos >= containerInfo.rowInfoArray[i].topPos) {
											currentIndex = i;
											break;
										}
									}
								} else {
									currentIndex = 0;
								}
								$container.drawMultipleSelectBox(startIndex, currentIndex, true, isKeepSelected);
							}
						} else if (mouseY > containerInfo.bottomPos) {
							if (currentIndex < rowSize - 1) {
								var targetPos = containerInfo.rowInfoArray[currentIndex].bottomPos + (mouseY - containerInfo.bottomPos);
								if (targetPos < containerInfo.scrollHeight) {
									for ( var i = currentIndex + 1; i < rowSize; i++) {
										if (targetPos < containerInfo.rowInfoArray[i].bottomPos) {
											currentIndex = i;
											break;
										}
									}
								} else {
									currentIndex = rowSize - 1;
								}
								$container.drawMultipleSelectBox(startIndex, currentIndex, true, isKeepSelected);
							}
						}
					});
				}).bind("mouseover", function(e1) {
					var childTarget = $(e1.target);
					if (this == childTarget.parent()[0]) {
						currentIndex = childTarget.data("index");
						$container.drawMultipleSelectBox(startIndex, currentIndex, true, isKeepSelected, true);
					}
				});

				/* IE hacked for mouse event */
				if ($.browser.msie) {
					$(document).bind("mouseleave." + PLUGIN_NAMESPACE, function() {
						$(this).one("mousemove." + PLUGIN_NAMESPACE, function(e1) {
							if (!e1.button) {
								validateMultipleSelectBox();
							}
						});
					});
				}

				/* trigger callback */
				if (options.onSelectStart) {
					$container.trigger("onSelectStart", [startIndex]);
				}
				return false;
			});
		}
		return $container;
	};

	/**
	 * Public : Get Cached Rows
	 * 
	 * @param isReNew
	 *            boolean
	 * @return jQuery
	 */
	$.fn.getCachedMultipleSelectBoxRows = function(isReNew) {
		return this.pushStack($.map(this, function(container) {
			var $container = $(container);
			var $rows = $container.data("rows");
			if (isReNew || !$rows) {
				$rows = $container.children("li");
				$container.data("rows", $rows);
			}
			return $rows.get();
		}));
	};

	/**
	 * Public : Draw Range
	 * 
	 * @param startIndex
	 *            int
	 * @param currentIndex
	 *            int
	 * @param isGetPositionByCache
	 *            boolean
	 * @param isSelectionSaved
	 *            boolean
	 * @param isScrollBarFrozen
	 *            boolean
	 * @return jQuery
	 */
	$.fn.drawMultipleSelectBox = function(startIndex, currentIndex, isGetPositionByCache, isSelectionSaved, isScrollBarFrozen) {
		return this.each( function() {
			var $container = $(this);
			var $rows = $container.getCachedMultipleSelectBoxRows();
			var options = $container.data("options");

			if (!isGetPositionByCache) {
				$container.recalculateMultipleSelectBox(true, true);
			}
			var containerInfo = $container.data("info");
			var isReversed = startIndex > currentIndex;

			/* remove invalid or duplicated request */
			if (startIndex < 0 || currentIndex < 0 || options.maxLimit == 0 || (startIndex == containerInfo.lastStartIndex && currentIndex == containerInfo.lastCurrentIndex) || $rows.eq(startIndex).hasClass(PLUGIN_STYLE_DISABLED)) {
				return this;
			}

			/* prepare */
			var stateArray = [];
			var selectedCount = 0;
			$rows.each( function(index) {
				var $childRow = $(this);
				/* clear and group by style */
				$childRow.removeClass(PLUGIN_STYLE_SELECTING);
				if (!$childRow.hasClass(PLUGIN_STYLE_DISABLED)) {
					if ($childRow.hasClass(PLUGIN_STYLE_SELECTED)) {
						if (isSelectionSaved) {
							selectedCount++;
							return this;
						}
						$childRow.removeClass(PLUGIN_STYLE_SELECTED);
					}
					stateArray[index] = $childRow;
				}
				return this;
			});

			/* calculate max limit */
			if (options.maxLimit > 0) {
				var maxLimit = options.maxLimit - selectedCount;
				if (maxLimit <= 0) {
					return this;
				}
				var resultCurrentIndex = currentIndex;
				if (isReversed) {
					for ( var i = startIndex; i >= currentIndex && maxLimit > 0; i--) {
						if (stateArray[i] != null) {
							resultCurrentIndex = i;
							maxLimit--;
						}
					}
				} else {
					for ( var i = startIndex; i <= currentIndex && maxLimit > 0; i++) {
						if (stateArray[i] != null) {
							resultCurrentIndex = i;
							maxLimit--;
						}
					}
				}
				currentIndex = resultCurrentIndex;
			}

			/* reset all style */
			$rows.eq(currentIndex).addClass(PLUGIN_STYLE_SELECTING);
			for ( var i = Math.min(startIndex, currentIndex); i <= Math.max(startIndex, currentIndex); i++) {
				if (stateArray[i] != null) {
					stateArray[i].addClass(PLUGIN_STYLE_SELECTED);
				}
			}

			/* reset history */
			containerInfo.lastStartIndex = startIndex;
			containerInfo.lastCurrentIndex = currentIndex;

			/* reset scroll bar */
			if (!isScrollBarFrozen) {
				$container.scrollTop(isReversed ? containerInfo.rowInfoArray[currentIndex].topPos : containerInfo.rowInfoArray[currentIndex].bottomPos - containerInfo.height);
			}
			return this;
		});
	};

	/**
	 * Public : Serialize MultipleSelectBox
	 * 
	 * @return resultArray
	 */
	$.fn.serializeMultipleSelectBox = function() {
		var resultArray = [];
		this.each( function() {
			var $container = $(this);
			var options = $container.data("options");
			resultArray.push($.map($container.getCachedMultipleSelectBoxRows(), function(row, index) {
				var $childRow = $(row);
				var resultValue = null;
				if (!$childRow.hasClass(PLUGIN_STYLE_DISABLED) && $childRow.hasClass(PLUGIN_STYLE_SELECTED) && (options.valueRendererArray == null || (resultValue = options.valueRendererArray[index]) == null)) {
					resultValue = $childRow.text();
				}
				return resultValue;
			}));
		});
		return resultArray;
	};

	/**
	 * Public : Yield MultipleSelectBox
	 * 
	 * @return jQuery
	 */
	$.fn.yieldMultipleSelectBox = function() {
		$(document).unbind("mouseleave." + PLUGIN_NAMESPACE + " mousemove." + PLUGIN_NAMESPACE);
		return this.unbind("mouseenter mouseleave mouseover");
	};

	/**
	 * Public : Destroy MultipleSelectBox
	 * 
	 * @return jQuery
	 */
	$.fn.destroyMultipleSelectBox = function() {
		/* yield event handler */
		return this.yieldMultipleSelectBox().each( function() {
			var $container = $(this);

			/* reset event handler */
			$container.unbind("mousedown onSelectStart onSelectEnd onSelectChange");

			/* clear cache */
			var $rows = $container.data("rows");
			if ($rows) {
				$rows.removeData("index");
			}
			$container.removeData("info").removeData("rows");
		});
	};

	/**
	 * Public : Recalculate MultipleSelectBox
	 * 
	 * @param isResetContainerInfo
	 *            boolean
	 * @param isResetRowsInfo
	 *            boolean
	 * @param isResetHistory
	 *            boolean
	 * @param isResetRowCache
	 *            boolean
	 * @return jQuery
	 */
	$.fn.recalculateMultipleSelectBox = function(isResetContainerInfo, isResetRowsInfo, isResetHistory, isResetRowCache) {
		return this.each( function() {
			var $container = $(this);
			var $rows = $container.getCachedMultipleSelectBoxRows(isResetRowCache);
			var containerInfo = $container.data("info");
			if (!containerInfo) {
				isResetContainerInfo = true;
				isResetRowsInfo = true;
				isResetHistory = true;
				containerInfo = {};
				$container.data("info", containerInfo);
			}

			/* reset all row's position or data */
			if (isResetRowsInfo) {
				var rowInfoArray = [];
				var firstTopPos = -1;
				$rows.each( function(index) {
					var $childRow = $(this);
					var childRowTopPos = $childRow.offset().top;
					if (index == 0) {
						firstTopPos = childRowTopPos;
					}
					childRowTopPos -= firstTopPos;

					$childRow.data("index", index);
					rowInfoArray.push({
						topPos : childRowTopPos,
						bottomPos : childRowTopPos + $childRow.outerHeight()
					});
				});
				containerInfo.rowInfoArray = rowInfoArray;
			}

			/* reset container's position or data */
			if (isResetContainerInfo) {
				containerInfo.topPos = $container.offset().top;
				containerInfo.bottomPos = containerInfo.topPos + $container.outerHeight();
				containerInfo.height = $container.innerHeight();
				containerInfo.scrollHeight = this.scrollHeight;
			}

			/* reset history data */
			if (isResetHistory) {
				containerInfo.lastStartIndex = -1;
				containerInfo.lastCurrentIndex = -1;
				containerInfo.prevStartIndex = -1;
				containerInfo.prevCurrentIndex = -1;
			}
		});
	};

	/**
	 * Public : MultipleSelectBox
	 * 
	 * @param options
	 *            class
	 * @return jQuery
	 */
	$.fn.multipleSelectBox = function(options) {
		options = $.extend({}, defaultOptions, options);
		return this.each( function() {
			var $container = $(this);

			/* prepare */
			$container.addClass(PLUGIN_NAMESPACE).data("options", options);

			/* disable text select */
			$container.css("MozUserSelect", "none").bind("selectstart", function() {
				return false;
			});

			/* destroy and recalculate */
			$container.destroyMultipleSelectBox().recalculateMultipleSelectBox();

			/* callback function */
			if (options.onSelectStart) {
				$container.bind("onSelectStart", options.onSelectStart);
			}
			if (options.onSelectEnd) {
				$container.bind("onSelectEnd", options.onSelectEnd);
			}
			if (options.onSelectChange) {
				$container.bind("onSelectChange", options.onSelectChange);
			}

			/* initialize */
			initializeMultipleSelectBox($container, options);
		});
	};

	/**
	 * Event Control
	 */
	$(document).bind("mouseup." + PLUGIN_NAMESPACE, function() {
		validateMultipleSelectBox();
	});
})(jQuery);