(function () {
  define([
    "slideout",
    'mage/translate',
    'Magento_Customer/js/customer-data'
  ], function (SlideoutRequire, $t, mageCustomerData) {
    'use strict';

    var requireJsLoadAttemptDone = false;
    var translator = $?.__ ?? $t;
    var customerDataRetriever = mageCustomerData;
    if (typeof customerData !== 'undefined') {
      customerDataRetriever = customerData;
    }
    var goBackButtons = document.querySelectorAll(".go-back-button");

    function removeDOMNode(element) {
      if (element) {
        element.remove();
      } else {
        console.warn('Element not found for the selector: ' + selector);
      }
    }

    // Helper hide element function
    var hide = function (elem, complete = false) {
      if (!elem) {
        return;
      }
      if (complete) {
        elem.style.display = 'none';
      } else {
        elem.style.visibility = 'hidden';
      }
    };

    // Helper show element function
    var show = function (elem) {
      if (!elem) {
        return;
      }
      elem.style.visibility = 'visible';
      elem.style.display = 'flex';
    };

    var switchMenuMode = function (mode, title) {
      if (mode === "default") {
        document.querySelector(".mobile-menu-header").classList.remove("mobile-menu-header-category");
        document.querySelector(".mobile-menu-header").classList.add("mobile-menu-header-default");
        goBackButtons.forEach((el) => {
          hide(el);
        });
      } else if (mode === "category") {
        document.querySelector(".mobile-menu-header").classList.add("mobile-menu-header-category");
        document.querySelector(".mobile-menu-header").classList.remove("mobile-menu-header-default");
        goBackButtons.forEach((el) => {
          show(el);
        });
        const loggedInClass = customerDataRetriever.get("customer")().firstname ? ".user-logged-in " : ".user-logged-out ";
        const menuTitle = document.querySelectorAll(loggedInClass + ".mobile-menu-menu-title");
        menuTitle.forEach((el) => {
          el.textContent = title;
        });
      }
    }

    const determineLogInView = function (breezeLoader = false) {
      if (!breezeLoader && !SlideoutRequire) {
        return false;
      }

      const helper = function (customer) {
        if (!firstName) {
          firstName = customer.firstname;
        }
        var userLoggedOutEls = document.querySelectorAll(".user-logged-out");
        var userLoggedInEls = document.querySelectorAll(".user-logged-in");

        if (firstName) {
          userLoggedOutEls.forEach((elem) => {
            removeDOMNode(elem);
          });
          userLoggedInEls.forEach((elem) => {
            show(elem);
          })
          document.querySelector(".mobile-menu-hello").textContent = translator("Hi, ") + firstName + "!";
        } else {
          userLoggedInEls.forEach((elem) => {
            removeDOMNode(elem);
          })
          userLoggedOutEls.forEach((elem) => {
            show(elem);
          })
        }
      }

      // just logged-in user does not have customer data initialized - get the name from the page
      var firstName;
      if (window.location.href.includes('customer/account/')) {
        var element = document.querySelector('.block-dashboard-info .box-information .box-content p');
        if (element) {
          var fullName = element.innerText.split('\n')[0];
          firstName = fullName.split(' ')[0];
        }
      }

      // Get the section data. For instance, 'customer' section.
      var customer = customerDataRetriever.get('customer')();

      // If data is not available in local storage, it will load from the server.
      if (!customer.data_id && requireJsLoadAttemptDone) {
        customerDataRetriever.reload(['customer'], false);
        customer = customerDataRetriever.get('customer')();
        // Now you have the customer data, you can execute your logic here.
        helper(customer)
      } else {
        // Customer data is already available, you can execute your logic here.
        helper(customer)
      }
    }

    // NON-BREEZE: DETERMINE LOGGED IN STATUS.
    // Get customer name upon loading it and display logged in / logged out elements accordingly
    determineLogInView();

    // BREEZE: DETERMINE LOGGED IN STATUS
    // Get customer name upon loading it and display logged in / logged out elements accordingly
    document.addEventListener('breeze:load', function () {
      determineLogInView(true)
    });

    // Same as above but on logged out page
    document.addEventListener('breeze:mount:Magento_Customer/js/logout-redirect', function () {
      document.querySelectorAll(".user-logged-out").forEach((elem) => {
        show(elem);
      });
      document.querySelectorAll(".user-logged-in").forEach((elem) => {
        hide(elem, true);
      })
    });

    // This line below makes sure that it works in both breeze and requirejs context (load method differs)
    const Slideout = window.Slideout ?? SlideoutRequire;

    // Make menu not appear when cookie consent bar is shown
    document.addEventListener('breeze:mount:Swissup_Gdpr/js/view/cookie-bar', (e, data) => {
      if (window.innerWidth < 640) {
        const shownCookieBar = document.querySelector(".cookie-bar.shown");
        if (shownCookieBar) {
          const cookieBar = document.querySelector(".page-wrapper");
          cookieBar.style.pointerEvents = "none";

          const acceptCookiesButton = document.querySelector(".accept-cookie-consent");
          acceptCookiesButton.addEventListener("click", () => {
            const cookieBar = document.querySelector(".page-wrapper");
            cookieBar.style.pointerEvents = "auto";
          });
        }
      }
    });

    // Init slideout
    var slideout = new Slideout({
      'panel': document.querySelector('.page-wrapper'),
      'menu': document.querySelector("#mobile-menu-container"),
      'padding': window.innerWidth - 64,
      'tolerance': 70
    });

    function disableSlideoutForElement(selector) {
      !!document.querySelector(selector) && document.querySelector(selector).addEventListener('touchstart', function () {
        slideout.disableTouch();
      });
    }

    // Disable slideout for multiple elements
    disableSlideoutForElement('.stage');
    disableSlideoutForElement('.thumbnails');
	disableSlideoutForElement('.glider-contain');
	disableSlideoutForElement('.block.related');
	disableSlideoutForElement('.block.upsell');
	disableSlideoutForElement('.block.crosssell');
	disableSlideoutForElement('.glider-new-products');

    // Re-enable slideout when touching anywhere outside of both .stage and .thumbnails
    document.addEventListener('touchstart', function (e) {
      if (!e.target.closest('.stage') && !e.target.closest('-thumbnails') && !e.target.closest('.glider-contain') && !e.target.closest('.block.related') && !e.target.closest('.block.upsell') && !e.target.closest('.block.crosssell') && !e.target.closest('.glider-new-products')) {
        slideout.enableTouch();
      }
    });

    // Attach "beforeopen" event listener to slideout to add an animation class
    slideout
      .on('beforeopen', function () {
        this.panel.classList.add('panel-open');
        // Make sure close button is visible at the top right corner when page is scrolled
        document.querySelector(".slideout-close").style.top = window.scrollY + 11 + "px";
      })
      .on('open', function () {
        this.panel.addEventListener('click', close);
      })
      .on('beforeclose', function () {
        this.panel.classList.remove('panel-open');
        this.panel.classList.add('panel-closing');
        this.panel.removeEventListener('click', close);
      })
      .on('close', function () {
        this.panel.classList.remove('panel-closing');
      });

    // Close slideout when clicked on close button
    document.querySelector('.slideout-close').addEventListener('click', function () {
      slideout.close();
    });

    // Open slideout when clicked on menu / hamburger button
    document.querySelector('.action.nav-toggle').addEventListener('click', function () {
      slideout.open();
    });

    // Init menu
    var menu = new MmenuLight(document.querySelector('.mm-spn'), 'all');
    var nav = menu.navigation({
      selected: 'active',
      title: "",
    });

    // Set default category if on category page
    const currentCategoryLinkTitle = document.querySelector(".mm-spn .category-item.active > a")?.textContent;
    const currentCategorySpanTitle = document.querySelector(".mm-spn .category-item.active > a > span")?.textContent;
    let currentCategoryTitle = currentCategoryLinkTitle;
    if (currentCategoryTitle) {
      currentCategoryTitle = currentCategorySpanTitle;
    }
    if (currentCategoryTitle) {
      nav.openPanel(document.querySelector(".mm-spn .category-item.active > ul"));
      switchMenuMode("category", currentCategoryTitle);
    }

    // Hide menu back button if no menu is open
    const panelStack = document.querySelectorAll(".mm-spn--open");
    if (panelStack.length === 1) {
      goBackButtons.forEach((el) => {
        hide(el);
      });
    }

    // Go back in menu hierarchy button
    // Show "category" menu header
    goBackButtons.forEach((el) => {
      el.addEventListener("click", () => {
        const panelStack = document.querySelectorAll(".mm-spn--open");
        if (panelStack.length > 1) {
          const currentPanel = [].slice.call(panelStack).pop();
          if (currentPanel) {
            const parentPanel = currentPanel.closest("ul.mm-spn--parent");
            if (parentPanel) {
              nav.openPanel(parentPanel);
              if (panelStack.length === 2) {
                switchMenuMode("default");
              } else {
                const menuTitle = parentPanel.closest("ul.mm-spn--parent").querySelector('a').textContent;
                switchMenuMode("category", menuTitle);
              }
            }
          }
        }
      });
    });

    // Timeout is a workaround for double-click on menu item
    var timeout = null;
    // Show "default" menu header
    document.querySelectorAll(".mm-spn li.category-item").forEach((link) => {
      link.addEventListener("click", (e) => {
        if (!timeout) {
          switchMenuMode("category", link.querySelector("a").textContent);
          timeout = true;
          setTimeout(() => {
            timeout = false;
          }, 300);
        }
      });
    });
  });
})()
