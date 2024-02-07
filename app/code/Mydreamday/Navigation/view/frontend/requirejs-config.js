var config = {
  paths: {
    'mobileMenu': 'Mydreamday_Navigation/js/mobile-menu',
    'slideout': 'Mydreamday_Navigation/js/slideout.min',
    'mmenuLight': 'Mydreamday_Navigation/js/mmenu-light',
  },
  shim: {
    'mobileMenu': {
      deps: ['slideout', 'mmenuLight']
    }
  }
};
