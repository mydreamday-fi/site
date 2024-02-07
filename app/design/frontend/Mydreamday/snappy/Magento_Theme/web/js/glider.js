/* @preserve
    _____ __ _     __                _
   / __// /(_)__/ /__  ____      (_)__
  / (_ // // // _  // -_)/ __/_    / /(_-<
  \__//_//_/ \_,_/ \__//_/  (_)__/ //__/
                              |__/

  Version: 1.7.4
  Author: Nick Piscitelli (pickykneee)
  Website: https://nickpiscitelli.com
  Documentation: http://nickpiscitelli.github.io/Glider.js
  License: MIT License
  Release Date: October 25th, 2018

*/

/* global define */

(function (factory) {
    typeof define === 'function' && define.amd
      ? define(factory)
      : typeof exports === 'object'
        ? (module.exports = factory())
        : factory()
  })(function () {
    ('use strict') // eslint-disable-line no-unused-expressions
  
    /* globals window:true */
    var _window = typeof window !== 'undefined' ? window : this
  
    var Glider = (_window.Glider = function (element, settings) {
      var __ = this
  
      if (element._glider) return element._glider
  
      __.ele = element
      __.ele.classList.add('glider')
  
      // expose glider object to its DOM element
      __.ele._glider = __
  
      // merge user setting with defaults
      __.opt = Object.assign(
        {},
        {
          slidesToScroll: 1,
          slidesToShow: 1,
          resizeLock: true,
          duration: 0.5,
          // easeInQuad
          easing: function (x, t, b, c, d) {
            return c * (t /= d) * t + b
          }
        },
        settings
      )
  
      // set defaults
      __.animate_id = __.page = __.slide = 0
      __.arrows = {}
  
      // preserve original options to
      // extend breakpoint settings
      __._opt = __.opt
  
      if (__.opt.skipTrack) {
        // first and only child is the track
        __.track = __.ele.children[0]
      } else {
        // create track and wrap slides
        __.track = document.createElement('div')
        __.ele.appendChild(__.track)
        while (__.ele.children.length !== 1) {
          __.track.appendChild(__.ele.children[0])
        }
      }
  
      __.track.classList.add('glider-track')
      window.trackk = __.track
      console.log(__.track)
      // start glider
      __.init()
  
      // set events
      __.resize = __.init.bind(__, true)
      __.event(__.ele, 'add', {
        scroll: __.updateControls.bind(__)
      })
      __.event(_window, 'add', {
        resize: __.resize
      })
    })
  
    var gliderPrototype = Glider.prototype
    gliderPrototype.init = function (refresh, paging) {
      var __ = this
  
      var width = 0
  
      var height = 0
        
      console.log(window.trackk)
      console.log(__.track)
      __.slides = __.track.children;
  
      [].forEach.call(__.slides, function (__, i) {
        __.classList.add('glider-slide')
        __.setAttribute('data-gslide', i)
      })
  
      __.containerWidth = __.ele.clientWidth
  
      var breakpointChanged = __.settingsBreakpoint()
      if (!paging) paging = breakpointChanged
  
      if (
        __.opt.slidesToShow === 'auto' ||
        typeof __.opt._autoSlide !== 'undefined'
      ) {
        var slideCount = __.containerWidth / __.opt.itemWidth
  
        __.opt._autoSlide = __.opt.slidesToShow = __.opt.exactWidth
          ? slideCount
          : Math.max(1, Math.floor(slideCount))
      }
      if (__.opt.slidesToScroll === 'auto') {
        __.opt.slidesToScroll = Math.floor(__.opt.slidesToShow)
      }
  
      __.itemWidth = __.opt.exactWidth
        ? __.opt.itemWidth
        : __.containerWidth / __.opt.slidesToShow;
  
      // set slide dimensions
      [].forEach.call(__.slides, function (__) {
        __.style.height = 'auto'
        __.style.width = __.itemWidth + 'px'
        width += __.itemWidth
        height = Math.max(__.offsetHeight, height)
      })
  
      __.track.style.width = width + 'px'
      __.trackWidth = width
      __.isDrag = false
      __.preventClick = false
      __.move = false
  
      __.opt.resizeLock && __.scrollTo(__.slide * __.itemWidth, 0)
  
      if (breakpointChanged || paging) {
        __.bindArrows()
        __.buildDots()
        __.bindDrag()
      }
  
      __.updateControls()
  
      __.emit(refresh ? 'refresh' : 'loaded')
    }
  
    gliderPrototype.bindDrag = function () {
      var __ = this
      __.mouse = __.mouse || __.handleMouse.bind(__)
  
      var mouseup = function () {
        __.mouseDown = undefined
        __.ele.classList.remove('drag')
        if (__.isDrag) {
          __.preventClick = true
        }
        __.isDrag = false
      }
  
      const move = function(){
        __.move = true
      };
  
      var events = {
        mouseup: mouseup,
        mouseleave: mouseup,
        mousedown: function (e) {
          e.preventDefault()
          e.stopPropagation()
          __.mouseDown = e.clientX
          __.ele.classList.add('drag')
          __.move = false
          setTimeout(move, 300);
        },
        touchstart: function (e) {
          __.ele.classList.add('drag')
          __.move = false
          setTimeout(move, 300);
        },
        mousemove: __.mouse,
        click: function (e) {
          if (__.preventClick && __.move) {
            e.preventDefault()
            e.stopPropagation()
          }
          __.preventClick = false
          __.move = false
        }
      }
  
      __.ele.classList.toggle('draggable', __.opt.draggable === true)
      __.event(__.ele, 'remove', events)
      if (__.opt.draggable) __.event(__.ele, 'add', events)
    }
  
    gliderPrototype.buildDots = function () {
      var __ = this
  
      if (!__.opt.dots) {
        if (__.dots) __.dots.innerHTML = ''
        return
      }
  
      if (typeof __.opt.dots === 'string') {
        __.dots = document.querySelector(__.opt.dots)
      } else __.dots = __.opt.dots
      if (!__.dots) return
  
      __.dots.innerHTML = ''
      __.dots.classList.add('glider-dots')
  
      for (var i = 0; i < Math.ceil(__.slides.length / __.opt.slidesToShow); ++i) {
        var dot = document.createElement('button')
        dot.dataset.index = i
        dot.setAttribute('aria-label', 'Page ' + (i + 1))
        dot.setAttribute('role', 'tab')
        dot.className = 'glider-dot ' + (i ? '' : 'active')
        __.event(dot, 'add', {
          click: __.scrollItem.bind(__, i, true)
        })
        __.dots.appendChild(dot)
      }
    }
  
    gliderPrototype.bindArrows = function () {
      var __ = this
      if (!__.opt.arrows) {
        Object.keys(__.arrows).forEach(function (direction) {
          var element = __.arrows[direction]
          __.event(element, 'remove', { click: element._func })
        })
        return
      }
      ['prev', 'next'].forEach(function (direction) {
        var arrow = __.opt.arrows[direction]
        if (arrow) {
          if (typeof arrow === 'string') arrow = document.querySelector(arrow)
          if (arrow) {
            arrow._func = arrow._func || __.scrollItem.bind(__, direction)
            __.event(arrow, 'remove', {
              click: arrow._func
            })
            __.event(arrow, 'add', {
              click: arrow._func
            })
            __.arrows[direction] = arrow
          }
        }
      })
    }
  
    gliderPrototype.updateControls = function (event) {
      var __ = this
  
      if (event && !__.opt.scrollPropagate) {
        event.stopPropagation()
      }
  
      var disableArrows = __.containerWidth >= __.trackWidth
  
      if (!__.opt.rewind) {
        if (__.arrows.prev) {
          __.arrows.prev.classList.toggle(
            'disabled',
            __.ele.scrollLeft <= 0 || disableArrows
          )
  
          __.arrows.prev.setAttribute(
            'aria-disabled',
            __.arrows.prev.classList.contains('disabled')
          )
        }
        if (__.arrows.next) {
          __.arrows.next.classList.toggle(
            'disabled',
            Math.ceil(__.ele.scrollLeft + __.containerWidth) >=
              Math.floor(__.trackWidth) || disableArrows
          )
  
          __.arrows.next.setAttribute(
            'aria-disabled',
            __.arrows.next.classList.contains('disabled')
          )
        }
      }
  
      __.slide = Math.round(__.ele.scrollLeft / __.itemWidth)
      __.page = Math.round(__.ele.scrollLeft / __.containerWidth)
  
      var middle = __.slide + Math.floor(Math.floor(__.opt.slidesToShow) / 2)
  
      var extraMiddle = Math.floor(__.opt.slidesToShow) % 2 ? 0 : middle + 1
      if (Math.floor(__.opt.slidesToShow) === 1) {
        extraMiddle = 0
      }
  
      // the last page may be less than one half of a normal page width so
      // the page is rounded down. when at the end, force the page to turn
      if (__.ele.scrollLeft + __.containerWidth >= Math.floor(__.trackWidth)) {
        __.page = __.dots ? __.dots.children.length - 1 : 0
      }
  
      [].forEach.call(__.slides, function (slide, index) {
        var slideClasses = slide.classList
  
        var wasVisible = slideClasses.contains('visible')
  
        var start = __.ele.scrollLeft
  
        var end = __.ele.scrollLeft + __.containerWidth
  
        var itemStart = __.itemWidth * index
  
        var itemEnd = itemStart + __.itemWidth;
  
        [].forEach.call(slideClasses, function (className) {
          /^left|right/.test(className) && slideClasses.remove(className)
        })
        slideClasses.toggle('active', __.slide === index)
        if (middle === index || (extraMiddle && extraMiddle === index)) {
          slideClasses.add('center')
        } else {
          slideClasses.remove('center')
          slideClasses.add(
            [
              index < middle ? 'left' : 'right',
              Math.abs(index - (index < middle ? middle : extraMiddle || middle))
            ].join('-')
          )
        }
  
        var isVisible =
          Math.ceil(itemStart) >= Math.floor(start) &&
          Math.floor(itemEnd) <= Math.ceil(end)
        slideClasses.toggle('visible', isVisible)
        if (isVisible !== wasVisible) {
          __.emit('slide-' + (isVisible ? 'visible' : 'hidden'), {
            slide: index
          })
        }
      })
      if (__.dots) {
        [].forEach.call(__.dots.children, function (dot, index) {
          dot.classList.toggle('active', __.page === index)
        })
      }
  
      if (event && __.opt.scrollLock) {
        clearTimeout(__.scrollLock)
        __.scrollLock = setTimeout(function () {
          clearTimeout(__.scrollLock)
          // dont attempt to scroll less than a pixel fraction - causes looping
          if (Math.abs(__.ele.scrollLeft / __.itemWidth - __.slide) > 0.02) {
            if (!__.mouseDown) {
              // Only scroll if not at the end (#94)
              if (__.trackWidth > __.containerWidth + __.ele.scrollLeft) {
                __.scrollItem(__.getCurrentSlide())
              }
            }
          }
        }, __.opt.scrollLockDelay || 250)
      }
    }
  
    gliderPrototype.getCurrentSlide = function () {
      var __ = this
      return __.round(__.ele.scrollLeft / __.itemWidth)
    }
  
    gliderPrototype.scrollItem = function (slide, dot, e) {
      if (e) e.preventDefault()
  
      var __ = this
  
      var originalSlide = slide
      ++__.animate_id
  
      var prevSlide = __.slide
      var position
  
      if (dot === true) {
        slide = Math.round(slide * __.containerWidth / __.itemWidth)
        position = slide * __.itemWidth
      } else {
        if (typeof slide === 'string') {
          var backwards = slide === 'prev'
  
          // use precise location if fractional slides are on
          if (__.opt.slidesToScroll % 1 || __.opt.slidesToShow % 1) {
            slide = __.getCurrentSlide()
          } else {
            slide = __.slide
          }
  
          if (backwards) slide -= __.opt.slidesToScroll
          else slide += __.opt.slidesToScroll
  
          if (__.opt.rewind) {
            var scrollLeft = __.ele.scrollLeft
            slide =
              backwards && !scrollLeft
                ? __.slides.length
                : !backwards &&
                  scrollLeft + __.containerWidth >= Math.floor(__.trackWidth)
                  ? 0
                  : slide
          }
        }
  
        slide = Math.max(Math.min(slide, __.slides.length), 0)
  
        __.slide = slide
        position = __.itemWidth * slide
      }
  
      __.emit('scroll-item', {prevSlide, slide});
  
      __.scrollTo(
        position,
        __.opt.duration * Math.abs(__.ele.scrollLeft - position),
        function () {
          __.updateControls()
          __.emit('animated', {
            value: originalSlide,
            type:
              typeof originalSlide === 'string' ? 'arrow' : dot ? 'dot' : 'slide'
          })
        }
      )
  
      return false
    }
  
    gliderPrototype.settingsBreakpoint = function () {
      var __ = this
  
      var resp = __._opt.responsive
  
      if (resp) {
        // Sort the breakpoints in mobile first order
        resp.sort(function (a, b) {
          return b.breakpoint - a.breakpoint
        })
  
        for (var i = 0; i < resp.length; ++i) {
          var size = resp[i]
          if (_window.innerWidth >= size.breakpoint) {
            if (__.breakpoint !== size.breakpoint) {
              __.opt = Object.assign({}, __._opt, size.settings)
              __.breakpoint = size.breakpoint
              return true
            }
            return false
          }
        }
      }
      // set back to defaults in case they were overriden
      var breakpointChanged = __.breakpoint !== 0
      __.opt = Object.assign({}, __._opt)
      __.breakpoint = 0
      return breakpointChanged
    }
  
    gliderPrototype.scrollTo = function (scrollTarget, scrollDuration, callback) {
      var __ = this
  
      var start = new Date().getTime()
  
      var animateIndex = __.animate_id
  
      var animate = function () {
        var now = new Date().getTime() - start
        __.ele.scrollLeft =
          __.ele.scrollLeft +
          (scrollTarget - __.ele.scrollLeft) *
            __.opt.easing(0, now, 0, 1, scrollDuration)
        if (now < scrollDuration && animateIndex === __.animate_id) {
          _window.requestAnimationFrame(animate)
        } else {
          __.ele.scrollLeft = scrollTarget
          callback && callback.call(__)
        }
      }
  
      _window.requestAnimationFrame(animate)
    }
  
    gliderPrototype.removeItem = function (index) {
      var __ = this
  
      if (__.slides.length) {
        __.track.removeChild(__.slides[index])
        __.refresh(true)
        __.emit('remove')
      }
    }
  
    gliderPrototype.addItem = function (ele) {
      var __ = this
  
      __.track.appendChild(ele)
      __.refresh(true)
      __.emit('add')
    }
  
    gliderPrototype.handleMouse = function (e) {
      var __ = this
      if (__.mouseDown) {
        __.isDrag = true
        __.ele.scrollLeft +=
          (__.mouseDown - e.clientX) * (__.opt.dragVelocity || 3.3)
        __.mouseDown = e.clientX
      }
    }
  
    // used to round to the nearest 0.XX fraction
    gliderPrototype.round = function (double) {
      var __ = this
      var step = __.opt.slidesToScroll % 1 || 1
      var inv = 1.0 / step
      return Math.round(double * inv) / inv
    }
  
    gliderPrototype.refresh = function (paging) {
      var __ = this
      __.init(true, paging)
    }
  
    gliderPrototype.setOption = function (opt, global) {
      var __ = this
  
      if (__.breakpoint && !global) {
        __._opt.responsive.forEach(function (v) {
          if (v.breakpoint === __.breakpoint) {
            v.settings = Object.assign({}, v.settings, opt)
          }
        })
      } else {
        __._opt = Object.assign({}, __._opt, opt)
      }
  
      __.breakpoint = 0
      __.settingsBreakpoint()
    }
  
    gliderPrototype.destroy = function () {
      var __ = this
  
      var replace = __.ele.cloneNode(true)
  
      var clear = function (ele) {
        ele.removeAttribute('style');
        [].forEach.call(ele.classList, function (className) {
          /^glider/.test(className) && ele.classList.remove(className)
        })
      }
      // remove track
      replace.children[0].outerHTML = replace.children[0].innerHTML
      clear(replace);
      [].forEach.call(replace.getElementsByTagName('*'), clear)
      __.ele.parentNode.replaceChild(replace, __.ele)
      __.event(_window, 'remove', {
        resize: __.resize
      })
      __.emit('destroy')
    }
  
    gliderPrototype.emit = function (name, arg) {
      var __ = this
  
      var e = new _window.CustomEvent('glider-' + name, {
        bubbles: !__.opt.eventPropagate,
        detail: arg
      })
      __.ele.dispatchEvent(e)
    }
  
    gliderPrototype.event = function (ele, type, args) {
      var eventHandler = ele[type + 'EventListener'].bind(ele)
      Object.keys(args).forEach(function (k) {
        eventHandler(k, args[k])
      })
    }
  
    return Glider
  })
  