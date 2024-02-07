(function () {
  'use strict';

  $.widget('menu', {
    component: 'menu',
    options: {
      menus: 'ul',
      dropdown: 'ul',
      useInlineDisplay: true,
      responsive: true,
      expanded: false,
      dropdownShowDelay: 100,
      dropdownHideDelay: 120,
      mediaBreakpoint: '(max-width: 767px)'
    },

    create: function () {
      var mql,
        self = this,
        themeBreakpoint = $('body').var('--navigation-media-mobile');

      if (this.options.responsive) {
        mql = window.matchMedia(themeBreakpoint || this.options.mediaBreakpoint);
        mql.addListener(this.toggleMode.bind(this));
        this.toggleMode(mql);
      } else {
        this.toggleDesktopMode();
      }

      this._setActiveMenu(); // varnish fix

      $('li.parent', this.element).on('keydown.menu', function (e) {
        var dropdown = $(this).children(self.options.dropdown),
          visibleDropdowns = $(self.options.dropdown + '.shown');

        if (['Enter', 'Escape', ' '].indexOf(e.key) === -1) {
          return;
        }

        if (e.key === 'Enter' && dropdown.hasClass('shown')) {
          return;
        }

        e.stopPropagation();

        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();

          visibleDropdowns.not(dropdown).each(function () {
            if (!$(this).has(dropdown.get(0)).length) {
              self.close($(this));
            }
          });

          if (dropdown.hasClass('shown')) {
            self.close(dropdown);
          } else {
            self.open(dropdown);
          }
        } else if (e.key === 'Escape' && visibleDropdowns.length) {
          self.close(visibleDropdowns.last());
        }
      });

      $('a', this.element).on('click.menu', '.ui-icon', function () {
        var dropdown = $(this).closest('a').siblings(self.options.dropdown);

        if (!dropdown.length) {
          return;
        }

        if (dropdown.hasClass('shown')) {
          self.close(dropdown);
        } else {
          self.open(dropdown);
        }

        return false;
      });
    },

    /** Hide expanded menu's, remove event listeneres */
    destroy: function () {
      $(this.options.dropdown + '.shown', this.element).each(function (i, dropdown) {
        this.close($(dropdown));
      }.bind(this));

      this._super();
    },

    /** Toggles between mobile and desktop modes */
    toggleMode: function (event) {
      if (event.matches) {
      } else {
        this.toggleDesktopMode();
      }
    },

    /** Enable desktop mode */
    toggleDesktopMode: function () {
      var self = this;

      $(self.options.dropdown + '.shown').each(function () {
        self.close($(this));
      });

      $('li.parent', this.element)
        .off('click.menu')
        .on('mouseenter.menu', function () {
          var dropdown = $(this).children(self.options.dropdown),
            delay = self.options.dropdownShowDelay;

          if (this.breezeTimeout) {
            clearTimeout(this.breezeTimeout);
            delete this.breezeTimeout;
          }

          if ($(self.options.dropdown + '.shown').length) {
            delay = 50;
          }

          this.breezeTimeout = setTimeout(function () {
            self.open(dropdown);
          }, delay);
        })
        .on('mouseleave.menu', function () {
          if (this.breezeTimeout) {
            clearTimeout(this.breezeTimeout);
            delete this.breezeTimeout;
          }

          this.breezeTimeout = setTimeout(function () {
            self.close($(this).children(self.options.dropdown));
          }.bind(this), self.options.dropdownHideDelay);
        });

      this._trigger('afterToggleDesktopMode');
    },

    open: function (dropdown) {
      this._trigger('beforeOpen', {
        dropdown: dropdown
      });

      dropdown.addClass('shown')
        .parent('li')
        .addClass('opened');

      if (this.options.useInlineDisplay) {
        dropdown.show();
      }
    },

    close: function (dropdown) {
      var eventData = {
        dropdown: dropdown,
        preventDefault: false
      };

      this._trigger('beforeClose', eventData);

      if (eventData.preventDefault === true) {
        return;
      }

      dropdown.removeClass('shown')
        .parent('li')
        .removeClass('opened');

      if (this.options.useInlineDisplay) {
        dropdown.hide();
      }

      this._trigger('afterClose', {
        dropdown: dropdown
      });
    },

    _setActiveMenu: function () {
      var currentUrl = window.location.href.split('?')[0];

      if (!this._setActiveMenuForCategory(currentUrl)) {
        this._setActiveMenuForProduct(currentUrl);
      }
    },

    _setActiveMenuForCategory: function (url) {
      var activeCategoryLink = this.element.find('a[href="' + url + '"]'),
        classes,
        classNav;

      if (!activeCategoryLink || !activeCategoryLink.parent().hasClass('li-item')) {
        return false;
      } else if (!activeCategoryLink.parent().hasClass('active')) {
        activeCategoryLink.parent().addClass('active');
        classes = activeCategoryLink.parent().attr('class');
        classNav = classes.match(/(nav\-)[0-9]+(\-[0-9]+)+/gi);

        if (classNav) {
          this._setActiveParent(classNav[0]);
        }
      }

      return true;
    },

    _setActiveParent: function (childClassName) {
      var parentElement,
        parentClass = childClassName.substr(0, childClassName.lastIndexOf('-'));

      if (parentClass.lastIndexOf('-') !== -1) {
        parentElement = this.element.find('.' + parentClass);

        if (parentElement) {
          parentElement.addClass('has-active');
        }
        this._setActiveParent(parentClass);
      }
    },

    _setActiveMenuForProduct: function (currentUrl) {
      var categoryUrlExtension,
        lastUrlSection,
        possibleCategoryUrl,
        //retrieve first category URL to know what extension is used for category URLs
        firstCategoryUrl = this.element.children('li').find('a').attr('href');

      if (firstCategoryUrl) {
        lastUrlSection = firstCategoryUrl.substr(firstCategoryUrl.lastIndexOf('/'));
        categoryUrlExtension = lastUrlSection.lastIndexOf('.') !== -1 ?
          lastUrlSection.substr(lastUrlSection.lastIndexOf('.')) : '';

        possibleCategoryUrl = currentUrl.substr(0, currentUrl.lastIndexOf('/')) + categoryUrlExtension;
        this._setActiveMenuForCategory(possibleCategoryUrl);
      }
    }
  });
})();
