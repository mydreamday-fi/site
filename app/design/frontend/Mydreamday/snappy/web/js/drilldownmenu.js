(function($){ 

    var pluginName = "ctDrillDown"; // name of plugin
    
    /** public methods **/
    var methods = {
        /** constructor **/
        init: function(options) {
            options = Object.assign({}, $.fn.ctDrillDown.defaults, options);
                
            return this.each(function() { 
                var $wrapper = $(this).addClass(options.myClass);
                var $menu = $wrapper.find("ul"); // Removed the ">" character from code
                
                // save data for later usage
                $wrapper.data(pluginName, {
                    options: options,
                    $menu: $menu,
                    path: new Array(),
                    pathNumerical: new Array(),
                    offset: $menu.width(),
                    keysActive: false
                });
                
                var defaultPath = new Array();
                
                // prepare markup & events
    
                // height of starting ul
				adjustHeight.apply($wrapper, [$menu]);
								
				// widths of uls
				var width = $wrapper[0].offsetWidth;
				$wrapper[0].style.width = width + "px";
				$menu.find("ul").each(function(index, ulElement) {
					ulElement.style.width = width + "px";
					ulElement.style.left = width + "px";
				});

                
                if(options.keyboardNavigation) {
                    var keysActive = false;
                    
                    // activate keyboard navigation (ctrl + m)
                    $(document).on("keyup", function(event) {
                        if(event.keyCode == 77 && event.ctrlKey) {
                            // ctrl + m clicked - focus on menu
                            var data = $wrapper.data(pluginName);
                            data.keysActive = !data.keysActive;
                            
                            if(data.keysActive) {
                                // if no position is active - activate first from the top
                                var path = $wrapper.data(pluginName).path;
                                var $li;
                                
                                if(path.length) {
                                    $li = $(">li:eq(0)", path[path.length-1].nextAll("ul"));
                                } else {
                                    $li = $(">li:eq(0)", $wrapper.data(pluginName).$menu);                                  
                                }
                                
                                $li.addClass(options.activeClass);
                            }
                        }
                        
                        return true;
                        
                    });
                    
                    // attach keyboard navigation listeners (arrows, enter)
                    $(document).bind("keyup", function(event){
                        var data = $wrapper.data(pluginName);
                      
                        if(!data.keysActive && $(":focus", $wrapper).length == 0) {
                            return true;
                        }
                        
                        switch(event.keyCode) {
                        case 8:
                        case 13:
                        case 37:
                        case 38:
                        case 39: 
                        case 40:
                            keyboardMove.apply($wrapper, [event.keyCode]);
                        break;
                        }
                        
                        return false;
                    });
                }

                if(options.ajax){
                    // prepare to ajax mode
                    
                    $(document).on(options.event, "li."+options.expandableClass+" > a", function(){
                        var $this = $(this);
                        
                        if(!$(this).nextAll("ul").length) {
                            // need to fetch the contents
                            $.ajax({
                                type: "GET",
                                url: $this.attr("href"),
                                dataType: "html",
                                success: function(data) {
                                    $this.after(data);
                                    prepareAjaxNode.apply($wrapper, $this.nextAll("ul"));
                                    $wrapper.ctDrillDown("goTo", $this);
                                }
                            });
                            
                        } else {
                            $wrapper.ctDrillDown("goTo", $(this));
                        }
                        
                        return false;
                    });
                } else {
                    // standard mode
                     
                    $("li >a", $menu).each(function() {
                        var expandable = false;
                        
                        if($(this).nextAll("ul").length > 0) {
                            $(this).parent().addClass(options.expandableClass);
                            expandable = true;
                        }
                        $(this).children().last().bind(options.event, function() {
                            var $this = $(this).parents('a').attr("href");
                         window.location.href = $this;
                          return false;
                        });
                        $(this).bind(options.event, function() {
                            if(!expandable) {return true;}

                            $wrapper.ctDrillDown("goTo", $(this));
                            var $drillDown = $("#drilldown");
                            var $current = $("#current");
                          
                            var $backb = $(".skin-classic-light.footer #back");
                            var breadcrumbs = $drillDown.ctDrillDown("getBreadcrumbs");
             
                            if(breadcrumbs.length < 2) {
                                $backb.fadeOut();
                            } else {
                                $backb.fadeIn();
                            }
                           
                            var current = breadcrumbs.pop();
                            $current.html(current.text());
                            return false;
                        });
                        

                        
                        // class based state holding
                        if($(this).hasClass(options.currentClass)) {
                            $(this).removeClass(options.currentClass);
                            
                            var $expandableLi = $(this).closest("li."+options.expandableClass);
                            var $realCurrent = $(">a", $expandableLi).addClass(options.currentClass);
                            
                            defaultPath = getNumericalPathForElement.apply($wrapper, [$realCurrent]);
                        }
                    });
    
                    // cookie based state holding
                    if(options.useCookies) {
                        var cookie = getCookie(pluginName);
                        
                        if(cookie != null && cookie != "") {
                            var cookiePath = cookie.split(',');
                            if(cookiePath.length > 0) {
                                defaultPath = cookiePath;
                            }
                        }
                    }
                    
                    // cookie based state has precedence with html based state
                    if(defaultPath.length > 0) {
                        followPath.apply($wrapper, [defaultPath, false]);
                        $wrapper.dequeue(pluginName);
                    }
                }
                
            });
        },
        /**
         * Open given position (node) in menu. 
         * 
         * @param $el node "a" element to be opened. If node does not exist in menu - menu will go to top. 
         * @param withAnimation boolean Should the operation be animated (optional, default: yes)
         * 
         */
        goTo: function($el, withAnimation) {
            return this.each(function() {
                var $wrapper = $(this),
                    data = $wrapper.data(pluginName),
                    pathNumerical = data.pathNumerical,
                    options = data.options;
                
                if(withAnimation == undefined) {
                    withAnimation = true;
                } 
                
                if($el == undefined || $el == null) {
                    // no element given = no action
                    return;
                }
                
                if (data.$menu.find($el).length === 0) {
					// given $el is not within our menu.
					// go top
					$wrapper.ctDrillDown("goTop", withAnimation);
					return;
				}
                
                if(!$el.closest("li").hasClass(options.expandableClass)) {
                    // we cannot open a thing that has no children
                    // so let's open the element that is above
                    $wrapper.ctDrillDown("goTo", $el.closest("li."+options.expandableClass).find(">a"), withAnimation);
                    
                    return;
                }
                
                var elPath = getNumericalPathForElement.apply($wrapper, [$el]);
                
                var pathNumericalLength = pathNumerical.length;
                var pathToFollow = new Array();
                
                for(var i = 0; i<elPath.length; i++) {
                    if(pathNumericalLength < i) {
                        pathToFollow = elPath.slice(i, elPath.length);
                        break;
                    }
                    
                    if(elPath[i] != pathNumerical[i]) {
                        // go up as many loops as needed
                        var stepsUp = pathNumerical.length - i;
                        for(var x=0; x<stepsUp; x++) {
                            closeCurrentPosition.apply($wrapper, [withAnimation]);
                        }
                        pathToFollow = elPath.slice(i, elPath.length);
                            
                        break;
                    }
                }
                
                if(elPath.length < pathNumerical.length && pathToFollow.length == 0) {
                    // go up as many times as the difference is
                    var stepsUp = pathNumerical.length - elPath.length;
                    for(var x=0; x<stepsUp; x++) {
                        closeCurrentPosition.apply($wrapper, [withAnimation]);
                    }
                }
                
                
                followPath.apply($wrapper, [pathToFollow, withAnimation]);
                $wrapper.dequeue(pluginName);
                
                return;
            });
        },
        /**
         * Go to the top of whole menu
         * 
         * @param withAnimation boolean Should the operation be animated (optional, default: true)
         */
        goTop: function(withAnimation) {
            return this.each(function() {
                var $wrapper = $(this),
                    data = $wrapper.data(pluginName);
                
                if(withAnimation == undefined) {
                    withAnimation = true;
                }
                
                $wrapper.ctDrillDown("goUp", data.path.length, withAnimation);
                
            });
        },
        /**
         * Go 'levels' up
         * 
         * @levels int How many levels to go
         * @withAnimation boolean Shall the movement be animated (optional, default: true)
         */
        goUp: function(levels, withAnimation) {
            if(withAnimation == undefined) {
                withAnimation = true;
            }
            
            return this.each(function() {
                if(levels == 0) {
                    // no going up
                    return;
                }
                
                var $wrapper = $(this);
                
                for(var i=0; i<levels; i++) {
                    closeCurrentPosition.apply($wrapper, [withAnimation]);
                }
                
                $wrapper.dequeue(pluginName);
                
                return;
            });
        },
        /** Get or set any option. If no value is specified, will act as a getter **/
        option: function(key, value) {
            if  (typeof key === "string" ) {
                if ( value === undefined ) {
                    // behave as a "getter"
                    var $container = $(this),
                        data = $container.data(pluginName);
                    
                    return data.options[key];
                } else {
                    // behave as a "setter"
                    var $container = $(this),
                        data = $container.data(pluginName);
                            
                    data.options[key] = value;
                    $container.data(pluginName, data);
                        
                    return this;
                }
            }
        },
        /**
         * Searches for given phrase in whole menu.
         * Always returns an array containing matched nodes.
         * 
         * 
         * @param phrase string The phrase we're looking for (at least 1 character)
         * @param autoOpen boolean If one node is matched, should the menu be automatically opened. Optional, default: true
         * @param withAnimation boolan (Used only if autoOpen is true) Shall the opening operation be animated
         */
        search: function(phrase, autoOpen, withAnimation) {
            if(phrase == "") {
                // don't allow to search for empty string
                return new Array();
            }
            if(autoOpen == undefined) {
                autoOpen = true;
            }
            
            if(withAnimation == undefined) {
                withAnimation = false;
            }
            
            var $wrapper = $(this),
                data = $wrapper.data(pluginName),
                options = data.options,
                regex = new RegExp(phrase, "i"),
                $menu = data.$menu;
            var elements = new Array();
            
            var $as = $("li > a", $menu);
            $as.filter("."+options.matchClass).removeClass(options.matchClass);
            
            $as.each(function() {
                if($(this).text().match(regex)) {
                    $(this).addClass(options.matchClass);
            
                    elements.push($(this));
                }
            });

            if(autoOpen && elements.length == 1) {
                var parentsLi = $(elements[0]).parents("li");
                var $parentEl;
                
                if(parentsLi.length < 2) {
                    // found element on the top 
                    $wrapper.ctDrillDown("goTop", withAnimation);
                } else {
                    $wrapper.ctDrillDown("goTo", $(">a", parentsLi[1]), withAnimation);
                }
            }

            return elements;
        },
        /*
         * Wrapper for "search" function - attaches all 
         * the events, builds search results.
         * 
         * @param $form node Search form
         * @param $resultsContainer node HTML container where the search results will be put
         * @param autoOpen boolean If one node is matched, should the menu be automatically opened. Optional, default: true
         * @param withAnimation boolean Shall the opening operation be animated 
         */
        searchWrapper: function($form, $resultsContainer, autoOpen, withAnimation) {
            return this.each(function() {
                var $wrapper = $(this);
                
                $form.submit(function() {
                    var phrase = $("input[type=text]", $form).val();
                    
                    var results = $wrapper.ctDrillDown("search", phrase, autoOpen, withAnimation);
                    
                    $resultsContainer.html("");
                    if(results.length > 1) {
                        var $sWrapper = $('<div></div>');
                        
                        $.each(results, function(i, res) {
                            var breadcrumb = $wrapper.ctDrillDown("getBreadcrumbs", res);
                            var breadcrumbHtml = $wrapper.ctDrillDown("getBreadcrumbsFormatted", breadcrumb, withAnimation);
                            
                            $sWrapper.append(breadcrumbHtml);
                        });
                        $resultsContainer.html($sWrapper);
                    }
                    
                    return false;
                });
                
            });
        },
        /**
         * Returns breadcrumbs to given element or to currently opened element as 
         * an array of nodes (Starting node is always "span")
         * 
         *@param $el node Node ("a" element) to get breadcrumbs to. If null (or undefined) given will return array to currently opened node.
         */
        getBreadcrumbs: function ($el){
            var $wrapper = $(this),
                data = $wrapper.data(pluginName);
        
            return getBreadcrumbs.apply($wrapper, [$el]);
        },
        /**
         * Returns HTML formatted with JS events attached breadcrumbs.
         * If no arguments are provided - returns breadcrumbs to currently opened node.
         * 
         * @param breadcrumbs Array|node Array of nodes to build path to. Optional, default: undefined
         * 
         */
        getBreadcrumbsFormatted: function(breadcrumbs, withAnimation) {
            var $wrapper = $(this);
            
            if(breadcrumbs == undefined) {
                breadcrumbs = $wrapper.ctDrillDown("getBreadcrumbs");
            }
            
            if(withAnimation == undefined) {
                withAnimation = true;
            }
            
            var $container = $('<div class="breadcrumbs"></div>');
            
            $.each(breadcrumbs, function(i, bc){
                var span = $('<span class="breadcrumb">'+$(bc).text()+'</span>').bind("click", function() {
                    $wrapper.ctDrillDown("goTo", bc, withAnimation);
                    
                    return false;
                });
                $container.append(span).append(" ");
            }); 
            
            return $container;
        }
    };
    

    /**
     * returns path to the element
     * 
     * @return Array indexes of "li" elements leading to given elements
     * 
     */
    var getNumericalPathForElement = function($element) {
		var $wrapper = $(this),
			data = this.dataset[pluginName] ? JSON.parse(this.dataset[pluginName]) : {},
			options = data.options,
			path = [];
			
		var currentElement = $element[0];
		while (currentElement && !currentElement.classList.contains(options.myClass)) {
			if (currentElement.tagName.toLowerCase() === 'li') {
				var siblings = Array.from(currentElement.parentElement.children);
				path.push(siblings.indexOf(currentElement));
			}
			currentElement = currentElement.parentElement;
		}
		
		path = path.reverse();
		
		return path;
	};

    
    
    /**
     * returns path to the element
     * 
     * @return Array jquery "a" elements leading to given elements
     */
    var getElementPathForElement = function($element) {
		var $wrapper = $(this),
			data = this.dataset[pluginName] ? JSON.parse(this.dataset[pluginName]) : {},
			options = data.options,
			path = [];
			
		var currentElement = $element[0];
		while (currentElement && !currentElement.classList.contains(options.myClass)) {
			if (currentElement.tagName.toLowerCase() === 'li') {
				var anchorChild = $(currentElement).children('a')[0];
				if (anchorChild) {
					path.push($(anchorChild));
				}
			}
			currentElement = currentElement.parentElement;
		}
		
		path = path.reverse();
		
		return path;
	};


    /**
     * Will follow given path from the position we're currently at
     */
    var followPath = function(pathToFollow, withAnimation) {
		var $wrapper = $(this),
			data = this.dataset[pluginName] ? JSON.parse(this.dataset[pluginName]) : {},
			$menu = data.$menu,
			pathNumerical = data.pathNumerical || [],
			path = data.path || [],
			options = data.options;
		
		if (pathToFollow.length === 0) {
			// nothing to follow
			return;
		}

		if (pathNumerical.length === 0) {
			// first step
			var targetLi = $menu[0].children[pathToFollow[0]];
			if (targetLi) {
				var targetA = targetLi.querySelector('a');
				openPosition.apply(this, [$(targetA), withAnimation]);
			}
		} else {
			// further step
			var $currentA = path[path.length - 1];
			var ulElement = $currentA[0].nextElementSibling;
			if (ulElement) {
				var liElements = ulElement.children;
				if (liElements[pathToFollow[0]]) {
					var $toBeOpenedA = $(liElements[pathToFollow[0]].querySelector('a'));
					openPosition.apply(this, [$toBeOpenedA, withAnimation]);
				}
			}
		}

		return followPath.apply(this, [pathToFollow.slice(1), withAnimation]);
	};

    
    
    /**
     * opens given position, assuming that the ancesting ul is currently visible
     */
    var openPosition = function($el, withAnimation) {
		var $wrapper = $(this),
			data = this.dataset[pluginName] ? JSON.parse(this.dataset[pluginName]) : {},
			options = data.options,
			path = data.path || [],
			pathNumerical = data.pathNumerical || [];
		
		var $prev = $el.closest("ul");
		var $next = $el.nextElementSibling;

		options.onBeforeOpen($el);

		var duration = options.duration;
		if(!withAnimation) {
			duration = 0;
		}

		// TODO: Handle your animation logic without using $.queue() and $.animate()

		path.push($el);
		pathNumerical.push(Array.prototype.indexOf.call($el.parentNode.children, $el));
		
		if(options.useCookies) {
			setCookie(pluginName, pathNumerical);
		}
		
		data.path = path;
		data.pathNumerical = pathNumerical;
		this.dataset[pluginName] = JSON.stringify(data);

		options.onOpened($el);
		
		var event = new CustomEvent("drilldownchange", { detail: [$el, true] });
		$wrapper[0].dispatchEvent(event);
		
		if(options.ajaxPreloading) {
			ajaxPreload.apply(this, [$el]);
		}
		return;
	};

    
    
    /**
     * 
     * Close currently opened node
     * 
     * @param withAnimation boolean Should it be animated (optional, default: yes)
     * 
     */
    var closeCurrentPosition = function(withAnimation) {
        var $wrapper = $(this),
            data = $wrapper.data(pluginName),
            $menu = data.$menu,
            options = data.options,
            path = data.path,
            pathNumerical = data.pathNumerical;
    
        
        var $current = path.pop();
        if($current == null) {
            // we're already on top, nothing to do
            return;
        }
        
        var $parent = $current.closest("ul");
        var $currentUl = $current.nextAll("ul");

        var duration = options.duration;
        if(withAnimation == false) {
            duration = 0;
        }
        
        options.onBeforeClose($current);

        $wrapper.queue(pluginName, function(next){
            $parent.show();
            $parent.animate({
                left: "+="+data.offset
            }, {
                duration: duration,
                easing: options.easing,
                complete: function() {
                    $currentUl.hide();
                    next();
                }
            });
            
            if(options.heightAutoAdjust) {
                adjustHeight.apply($wrapper, [$parent]);
            }
        });
        
        pathNumerical.pop();
        
        if(options.useCookies) {
            setCookie(pluginName, pathNumerical);
        }
        
        data[pathNumerical] = pathNumerical;
        data[path] = path;
        $wrapper.data(pluginName, data);
        
        var $el = path[path.length-1];
        options.onClosed($current);
        $wrapper.trigger("drilldownchange", [$el, false]);
    };
    
    
    /**
     * Returns breadcrumbs (starter + path) to the given element or
     * to the currently opened element
     *
     * @param $el node Element to get breadcrumbs to. Leave it undefined to get breadcrumbs to currently opened
     */
    var getBreadcrumbs = function($el) {
        var $wrapper = $(this),
            data = $wrapper.data(pluginName),
            options = data.options;
        
        var start = new Array();
        start.push($('<span class="">'+options.startName+"</span>"));
        
        var path = data.path;
        if($el != undefined) {
            path = getElementPathForElement.apply($wrapper, [$el]);
        }
        
        return start.concat(path);
            
    };
    
    
    /**
     * Cookie handler - setter
     */
    var setCookie = function (name,value,days) {
        if (days) {
            var date = new Date();
            date.setTime(date.getTime()+(days*24*60*60*1000));
            var expires = "; expires="+date.toGMTString();
        }
        else var expires = "";
        document.cookie = name+"="+value+expires+"; path=/";
    };
    

    /**
     * Cookie handler -getter
     */
    var getCookie = function (name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }
        return null;
    };

    /**
     * Adjusts height of given $ul basing on it's content
     * 
     * @param $ul node 
     */
	
	var adjustHeight = function($ul) {
		var $wrapper = $(this),
			dataHeight = $ul[0].dataset.pluginHeight, // Using dataset to fetch stored height
			newHeight;

		if (dataHeight !== undefined) {
			newHeight = parseInt(dataHeight, 10); // Convert string data to integer
		} else {
			newHeight = $ul[0].offsetHeight;
			$ul[0].dataset.pluginHeight = newHeight; // Storing height in dataset
		}

		// The animation section remains unchanged
		var startHeight = $wrapper[0].offsetHeight;
		var change = newHeight - startHeight;
		var startTime = performance.now();
		var duration = 200;

		function animate(time) {
			var runtime = time - startTime;
			var progress = Math.min(runtime / duration, 1);

			$wrapper[0].style.height = (startHeight + change * progress) + 'px';

			if (runtime < duration) {
				requestAnimationFrame(animate);
			}
		}

		requestAnimationFrame(animate);
	};


    
    /**
     * Preloads all
     * positions in given $el
     * 
     * @param $el node "a" element to preload subpositions of
     */
    var ajaxPreload = function($el) {
        var $wrapper = $(this),
            data = this.pluginData, // Let's assume you've set this property somewhere else.
            options = data.options;
        
        // Custom function to mimic jQuery's nextAll
		function nextAll(element, selector) {
			var siblings = [];
			while (element = element.nextElementSibling) {
				if (!selector || element.matches(selector)) {
					siblings.push(element);
				}
			}
			return $(siblings); // Convert array to Cash collection
		}

		nextAll($el[0], "ul").find("li." + options.expandableClass + ">a").each(function(index, elem) {
			var $this = $(elem);
			if ($this.nextAll("ul").length > 0) {
				// element already has children
				return;
			}

			// Using Fetch API for AJAX
			fetch($this.attr("href"))
				.then(response => response.text())
				.then(data => {
					$this.after(data);
					prepareAjaxNode.apply($wrapper[0], [$this.nextAll("ul")[0]]);
				})
				.catch(error => {
					console.error("Error fetching data:", error);
				});
		});
	};

    /**
     * Prepares freshly added thru ajax ul node 
     */
    var prepareAjaxNode = function($ul) {
        var $wrapper = $(this),
            data = $wrapper.data(pluginName);
        
        $($ul).css("width", data.$menu.width()+"px").css("left", data.$menu.width()+"px");
        data.$menu = $(">ul", $wrapper);
        $wrapper.data(pluginName, data);
    };
    
    
    /**
     * Change menu position
     */
    var keyboardMove = function(direction) {
		var $wrapper = $(this),
			data = $wrapper.data(pluginName),
			path = data.path,
			options = data.options,
			$ul = data.$menu;
		
		if(path.length > 0) {
			var $current = path[path.length-1];
			$ul = $current.next('ul');
		} else {
			if(direction == 37) {
				// if we're on top and want to move up - do nothing
				return;
			}
		}
		
		var $activeLi = $ul.find('> li.' + options.activeClass).eq(0);
		
		if(direction == 37 || direction == 8) {
			// go up one step (we already know we're not on top)
			
			$wrapper.ctDrillDown("goUp", 1);
			$activeLi.removeClass(options.activeClass);
			$ul.parent().parent('ul').children('li').eq(0).addClass(options.activeClass); 
			
			return;
		}
		
		if(direction == 39 || direction == 13) {
			// go down (into)
			var $active = $activeLi.children('a').eq(0);
			
			if(!$activeLi.hasClass(options.expandableClass)) {
				// no move - this li is not expandable
				if(direction == 13 && $active.length) {
					window.location = $active.attr("href");
				}
				
				return;
			}
			
			if($active.length) {
				$wrapper.ctDrillDown("goTo", $active);
				$activeLi.removeClass(options.activeClass);
				$active.next('ul').children('li').eq(0).addClass(options.activeClass);
			}
			
			return;
		}
		
		// now we could go left or right only
		
		if(direction == 40) {
			// go down
			var $newLi = $activeLi.next();
			if($newLi.length == 0) {
				$newLi = $ul.children('li').eq(0);
			}
		} else if(direction == 38) {
			// go up
			var $newLi = $activeLi.prev();
			if($newLi.length == 0) {
				$newLi = $ul.children('li').last();
			}
		} 
		
		if($newLi.length > 0) {
			// change active only if we're not first element
			$activeLi.removeClass(options.activeClass);
			$newLi.addClass(options.activeClass);
		}
		
		return;
	};

    
    $.fn.ctDrillDown = function(method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.from(arguments).slice(1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			throw new Error('Method ' +  method + ' does not exist on Drill Down!');
		}
	};
    
    
    // If you're completely replacing jQuery with Cash, 
	// you can use $ as the alias for Cash. Otherwise, replace $ with the correct alias.
	$.fn.ctDrillDownDefaults = {
		/** default values for plugin options **/
		
		myClass: "ctDrillDown", // class given automatically to the element
		expandableClass: "expandable", // Non ajax mode: this class will be given to "li" with children. Ajax mode: give this class to "li" that has children.
		onOpened: function() {}, // Callback fired just after submenu was opened (element passed as an argument). Replacing $.noop.
		onBeforeOpen: function() {}, // Callback fired just before submenu opening (element passed as an argument). Replacing $.noop.
		onClosed: function() {}, // Callback fired just after submenu was closed (closed element passed as an argument). Replacing $.noop.
		onBeforeClose: function() {}, // Callback fired just before submenu is closed (closing element passed as an argument). Replacing $.noop.
		duration: 300, // Opening/closing animation time
		easing: "linear", // Type of easing (see jquery ui docs for more)
		currentClass: "current", // Menu will automatically open on the element having this class on load (think of stateful menu)
		useCookies: false, // Set it true to activate cookie-based stateful
		heightAutoAdjust: true, // If true, plugin will update the height of container automatically based on content
		matchClass: "match", // class given to element found by search method
		startName: "Start", // name of first breadcrumb
		ajax: false, // set true to enable ajax mode
		event: "click", // event on which to open submenu
		keyboardNavigation: true, // is keyboard navigation active
		activeClass: "active" // currently active position, i.e. selected on keyboard
	};

});
