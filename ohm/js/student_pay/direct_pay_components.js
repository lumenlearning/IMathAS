// version 1.0.9

var directPayComponents = (function (React) {
'use strict';

var classCallCheck = function (instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
};

var createClass = function () {
  function defineProperties(target, props) {
    for (var i = 0; i < props.length; i++) {
      var descriptor = props[i];
      descriptor.enumerable = descriptor.enumerable || false;
      descriptor.configurable = true;
      if ("value" in descriptor) descriptor.writable = true;
      Object.defineProperty(target, descriptor.key, descriptor);
    }
  }

  return function (Constructor, protoProps, staticProps) {
    if (protoProps) defineProperties(Constructor.prototype, protoProps);
    if (staticProps) defineProperties(Constructor, staticProps);
    return Constructor;
  };
}();

var _extends = Object.assign || function (target) {
  for (var i = 1; i < arguments.length; i++) {
    var source = arguments[i];

    for (var key in source) {
      if (Object.prototype.hasOwnProperty.call(source, key)) {
        target[key] = source[key];
      }
    }
  }

  return target;
};

var inherits = function (subClass, superClass) {
  if (typeof superClass !== "function" && superClass !== null) {
    throw new TypeError("Super expression must either be null or a function, not " + typeof superClass);
  }

  subClass.prototype = Object.create(superClass && superClass.prototype, {
    constructor: {
      value: subClass,
      enumerable: false,
      writable: true,
      configurable: true
    }
  });
  if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass;
};

var possibleConstructorReturn = function (self, call) {
  if (!self) {
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  }

  return call && (typeof call === "object" || typeof call === "function") ? call : self;
};

var DirectPayButton = function (_React$Component) {
  inherits(DirectPayButton, _React$Component);

  function DirectPayButton(props) {
    classCallCheck(this, DirectPayButton);

    var _this = possibleConstructorReturn(this, (DirectPayButton.__proto__ || Object.getPrototypeOf(DirectPayButton)).call(this, props));

    _this._openCheckout = _this._openCheckout.bind(_this);
    return _this;
  }

  createClass(DirectPayButton, [{
    key: 'componentDidMount',
    value: function componentDidMount() {
      var scriptEl = document.createElement('script');
      scriptEl.setAttribute('src', 'https://checkout.stripe.com/checkout.js');
      scriptEl.setAttribute('class', 'stripe-button');
      scriptEl.setAttribute('data-key', this.props.stripeKey);
      scriptEl.setAttribute('data-amount', this.props.chargeAmount);
      scriptEl.setAttribute('data-name', 'Lumen Learning');
      scriptEl.setAttribute('data-description', this.props.chargeDescription);
      scriptEl.setAttribute('data-image', this.props.stripeModalLogoUrl);
      scriptEl.setAttribute('data-locale', 'auto');
      scriptEl.setAttribute('data-zip-code', 'true');
      scriptEl.setAttribute('data-allow-remember-me', 'false');
      scriptEl.setAttribute('data-label', 'Pay Now');
      document.getElementById('payment-button-form').appendChild(scriptEl);
    }
  }, {
    key: 'render',
    value: function render() {
      return React.createElement(
        'div',
        { id: 'payment-button-container', className: 'form-control', style: { paddingBottom: '13px', paddingTop: '13px' } },
        React.createElement('form', { id: 'payment-button-form', action: this.props.endpointUrl, onClick: this._openCheckout, method: 'POST' })
      );
    }
  }, {
    key: '_openCheckout',
    value: function _openCheckout() {
      return {
        image: this.props.image,
        name: this.props.institutionName,
        description: this.props.chargeDescription,
        amount: this.props.chargeAmount
      };
    }
  }]);
  return DirectPayButton;
}(React.Component);

var styles = {
  parentWrapper: {
    display: 'flex',
    paddingLeft: '35px'
  },
  leftColumn: {
    display: 'flex',
    flexDirection: 'column',
    flexBasis: '50%',
    paddingRight: '64px',
    maxWidth: '360px'
  },
  confirmationPageWrapper: {
    fontFamily: 'Libre Franklin, sans serif !important',
    margin: '2.5em 1.75em',
    color: '#212b36'
  },
  activationHeading: {
    fontSize: '26px',
    fontWeight: 'bold',
    fontStyle: 'normal',
    fontStretch: 'normal',
    lineHeight: '1.23',
    letterSpacing: 'normal',
    textAlign: 'left',
    color: '#212b36'
  },
  courseTitle: {
    fontSize: '16px',
    fontWeight: 'normal',
    fontStyle: 'normal',
    fontStretch: 'normal',
    lineHeight: '1.5',
    letterSpacing: 'normal',
    textAlign: 'left',
    color: '#212b36',
    marginBottom: '17px',
    marginTop: '17px'
  },
  termsAndPrivacy: {
    fontSize: '12px',
    fontWeight: 'normal',
    fontStyle: 'normal',
    fontStretch: 'normal',
    lineHeight: '1.33',
    letterSpacing: 'normal',
    textAlign: 'left',
    color: '#212b36'
  },
  rightColumn: {
    display: 'flex',
    flexDirection: 'column',
    flexBasis: '45%',
    paddingTop: '10px'
  },
  lumenLogo: {
    height: '41px',
    marginLeft: '1em',
    width: '88.7px'
  },
  schoolLogo: {
    height: '100%',
    width: '100%',
    display: 'block'
  },
  schoolLogoWrapper: {
    width: '224px',
    paddingBottom: '50px'
  },
  lumenAttributionWrapper: {
    display: 'flex',
    alignItems: 'center',
    fontSize: '12px',
    paddingLeft: '35px'
  },
  smallBlockHeaders: {
    width: '240px',
    height: '16px',
    fontSize: '12px',
    fontWeight: '600',
    fontStyle: 'normal',
    fontStretch: 'normal',
    letterSpacing: 'normal',
    textAlign: 'left',
    color: '#212b36',
    lineHeight: '1.6em'
  },
  bottomSmallBlockHeaders: {
    width: '240px',
    height: '16px',
    fontSize: '12px',
    fontWeight: '600',
    fontStyle: 'normal',
    fontStretch: 'normal',
    letterSpacing: 'normal',
    textAlign: 'left',
    color: '#212b36',
    lineHeight: '1.6em',
    marginBottom: '12px'
  },
  topSmallBlockText: {
    width: '267px',
    height: '60px',
    fontSize: '14px',
    fontWeight: 'normal',
    fontStyle: 'normal',
    fontStretch: 'normal',
    lineHeight: '1.43',
    letterSpacing: 'normal',
    textAlign: 'left',
    color: '#212b36',
    marginTop: '5px',
    marginBottom: '32px'
  },
  bottomSmallBlockText: {
    width: '267px',
    height: '60px',
    fontSize: '14px',
    fontWeight: 'normal',
    fontStyle: 'normal',
    fontStretch: 'normal',
    lineHeight: '1.43',
    letterSpacing: 'normal',
    textAlign: 'left',
    color: '#212b36',
    marginTop: '5px'
  },
  twoWeekTrialText: {
    width: '309px',
    height: '20px',
    fontSize: '14px',
    fontWeight: 'normal',
    fontStyle: 'normal',
    fontStretch: 'normal',
    lineHeight: '2',
    letterSpacing: 'normal',
    textAlign: 'left',
    color: '#1e74d1',
    textDecoration: 'underline'
  }
};

var DirectPayCourseActivation = function (_React$Component) {
  inherits(DirectPayCourseActivation, _React$Component);

  function DirectPayCourseActivation(props) {
    classCallCheck(this, DirectPayCourseActivation);

    var _this = possibleConstructorReturn(this, (DirectPayCourseActivation.__proto__ || Object.getPrototypeOf(DirectPayCourseActivation)).call(this, props));

    _this.state = {
      windowWidth: null
    };

    _this._handleWindowResize = _this._handleWindowResize.bind(_this);
    return _this;
  }

  createClass(DirectPayCourseActivation, [{
    key: 'componentWillMount',
    value: function componentWillMount() {
      this.setState({
        windowWidth: this._getWindowSize()
      });

      window.addEventListener("resize", this._handleWindowResize);
    }
  }, {
    key: 'render',
    value: function render() {
      var termsOfServiceURL = "https://lumenlearning.com/policies/terms-of-service/";
      var privacyPolicy = "https://lumenlearning.com/policies/privacy-policy/";
      var priceInDollars = (this.props.chargeAmount / 100).toLocaleString('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2 });

      return React.createElement(
        'div',
        null,
        React.createElement(
          'div',
          { style: _extends({}, styles.parentWrapper, this._getParentStyle()) },
          React.createElement(
            'div',
            { className: 'left-column', style: _extends({}, styles.leftColumn, this._getTabletSpecs()) },
            React.createElement(
              'h1',
              { className: 'activation-heading', style: styles.activationHeading },
              'One-time Course Activation'
            ),
            React.createElement(
              'h3',
              { className: 'course-title',
                style: styles.courseTitle },
              this.props.courseTitle + ': ' + priceInDollars
            ),
            window.innerWidth < 900 ? React.createElement(
              'p',
              { className: 'top-small-block-text', style: styles.topSmallBlockText },
              'This low-cost activation is only required for assessments. Course content is always available.'
            ) : "",
            React.createElement(
              'p',
              { className: 'terms-and-privacy', style: styles.termsAndPrivacy },
              'By clicking on Pay Now or by starting a trial you agree to the Lumen Learning ',
              React.createElement(
                'a',
                { href: termsOfServiceURL, target: "_blank", style: { textDecoration: 'underline', color: '#212b36' } },
                'Terms of Service'
              ),
              ' and ',
              React.createElement(
                'a',
                { href: privacyPolicy, target: "_blank", style: { textDecoration: 'underline', color: '#212b36' } },
                'Privacy Policy'
              ),
              '.'
            ),
            React.createElement(DirectPayButton, {
              paymentStatus: this.props.paymentStatus,
              stripeKey: this.props.stripeKey,
              chargeAmount: this.props.chargeAmount,
              institutionName: this.props.institutionName,
              chargeDescription: this.props.chargeDescription,
              stripeModalLogoUrl: this.props.stripeModalLogoUrl,
              endpointUrl: this.props.endpointUrl,
              userEmail: this.props.userEmail
            })
          ),
          this._renderRightColumn()
        )
      );
    }
  }, {
    key: 'componentWillUnmount',
    value: function componentWillUnmount() {
      window.removeEventListener("resize");
    }
  }, {
    key: '_renderRightColumn',
    value: function _renderRightColumn() {
      if (window.innerWidth < 900) {
        return this._renderSmallBlockDecision();
      } else {
        return React.createElement(
          'div',
          { className: 'right-column', style: styles.rightColumn },
          React.createElement(
            'div',
            { style: { display: 'flex', flexDirection: 'row' } },
            React.createElement('img', {
              src: 'https://s3-us-west-2.amazonaws.com/lumen-components-prod/assets/icons/icon-info.svg',
              alt: 'information icon',
              style: { width: '24', paddingRight: '5px', objectFit: 'contain' }
            }),
            React.createElement(
              'p',
              { className: 'small-block-headers', style: styles.smallBlockHeaders },
              'GOOD TO KNOW!'
            )
          ),
          React.createElement(
            'p',
            { className: 'top-small-block-text', style: styles.topSmallBlockText },
            'This low-cost activation is only required for assessments. Course content is always available.'
          ),
          this._renderSmallBlockDecision()
        );
      }
    }
  }, {
    key: '_renderSmallBlockDecision',
    value: function _renderSmallBlockDecision() {
      var renderStatusBasedText = this._renderStatusBasedText();
      var redirectTo = this.props.redirectTo;

      if (this.props.paymentStatus === 'in_trial' || this.props.paymentStatus === 'trial_not_started') {
        return React.createElement(
          'div',
          null,
          React.createElement(
            'p',
            { className: 'bottom-small-block-headers', style: styles.bottomSmallBlockHeaders },
            renderStatusBasedText.smallHeader
          ),
          React.createElement(
            'p',
            { className: 'bottom-small-block-text', style: styles.bottomSmallBlockText },
            renderStatusBasedText.smallText
          ),
          React.createElement(
            'a',
            { href: redirectTo, className: 'two-week-trial-text', style: styles.twoWeekTrialText },
            renderStatusBasedText.smallLink
          )
        );
      }
    }
  }, {
    key: '_renderStatusBasedText',
    value: function _renderStatusBasedText() {
      var trialTimeRemaining = this._getTrialTimeRemainingWords();
      if (this.props.paymentStatus === 'in_trial') {
        return {
          smallHeader: "CONTINUE TRIAL",
          smallText: "You can continue to access your assessments for " + trialTimeRemaining + " before activation is required.",
          smallLink: "Continue Trial"
        };
      } else if (this.props.paymentStatus === 'trial_not_started') {
        return {
          smallHeader: "NOT READY TO PAY?",
          smallText: "You can access your assessments for two weeks before activation is required.",
          smallLink: "Start Two-week Trial"
        };
      }
    }
  }, {
    key: '_getTrialTimeRemainingWords',
    value: function _getTrialTimeRemainingWords() {
      var timeLeft = this.props.trialTimeRemaining;
      if (60 > timeLeft) {
        timeLeft = 'less than 1 minute';
      } else if (60 < timeLeft && 120 > timeLeft) {
        timeLeft = '1 minute';
      } else if (3600 >= timeLeft) {
        timeLeft = Math.floor(timeLeft / 60) + ' minutes';
      } else if (3600 <= timeLeft && 7200 > timeLeft) {
        timeLeft = Math.floor(timeLeft / 3600) + ' hour';
      } else if (86400 > timeLeft) {
        timeLeft = Math.floor(timeLeft / 3600) + ' hours';
      } else if (86400 < timeLeft && 172800 > timeLeft) {
        timeLeft = '1 day';
      } else {
        timeLeft = (timeLeft / 86400).toFixed() + ' days';
      }
      return timeLeft;
    }
  }, {
    key: '_getWindowSize',
    value: function _getWindowSize() {
      return window.innerWidth;
    }
  }, {
    key: '_handleWindowResize',
    value: function _handleWindowResize(e) {
      this.setState({
        windowWidth: this._getWindowSize()
      });
    }
  }, {
    key: '_getParentStyle',
    value: function _getParentStyle() {
      var styles$$1 = {};

      if (this.state.windowWidth < 900) {
        styles$$1 = {
          flexDirection: 'column'

        };
      }

      return styles$$1;
    }
  }, {
    key: '_getTabletSpecs',
    value: function _getTabletSpecs() {
      var styles$$1 = {};

      if (this.state.windowWidth < 900 && this.state.windowWidth > 414) {
        styles$$1 = {
          width: '340px'
        };
      }

      return styles$$1;
    }
  }]);
  return DirectPayCourseActivation;
}(React.Component);

var styles$1 = {
  confirmationPageWrapper: {
    fontFamily: 'Libre Franklin, sans serif',
    color: '#212b36'
  },
  continueButton: {
    backgroundColor: '#166cc8',
    border: '1px solid #1064c0',
    borderRadius: '3px',
    color: '#fff',
    cursor: 'pointer',
    fontSize: '14px',
    height: '36px',
    margin: '2em 0 5em',
    width: '91px',
    textAlign: 'center',
    fontWeight: 'normal',
    padding: '0px'
  },
  confirmationHeading: {
    fontSize: '26px',
    fontWeight: 'bold',
    lineHeight: '1.23'
  },
  confirmationSubheading: {
    fontSize: '16px',
    fontWeight: 'normal',
    lineHeight: '1.5',
    marginTop: '17px'
  },
  confirmationText: {
    margin: '0.25em',
    fontSize: '12px',
    lineHeight: '1.33'
  },
  confirmationTextWrapper: {
    fontSize: '12px',
    marginTop: '1.25em'
  },
  confirmationWrapper: {
    paddingLeft: '35px'
  },
  lumenAttributionWrapper: {
    display: 'flex',
    alignItems: 'center',
    fontSize: '12px'
  },
  lumenLogo: {
    height: '41px',
    marginLeft: '1em',
    width: '88.7px'
  },
  schoolLogo: {
    height: '100%',
    width: '100%'
  },
  schoolLogoWrapper: {
    height: '69px',
    width: '224px'
  }
};

var DirectPayConfirmation = function (_React$Component) {
  inherits(DirectPayConfirmation, _React$Component);

  function DirectPayConfirmation(props) {
    classCallCheck(this, DirectPayConfirmation);

    var _this = possibleConstructorReturn(this, (DirectPayConfirmation.__proto__ || Object.getPrototypeOf(DirectPayConfirmation)).call(this, props));

    _this._handleClick = _this._handleClick.bind(_this);
    return _this;
  }

  createClass(DirectPayConfirmation, [{
    key: 'render',
    value: function render() {
      return React.createElement(
        'div',
        { style: styles$1.confirmationPageWrapper },
        React.createElement(
          'div',
          { className: 'confirmation-wrapper', style: styles$1.confirmationWrapper },
          React.createElement(
            'h1',
            { className: 'heading', style: styles$1.confirmationHeading },
            'Thank You!'
          ),
          React.createElement(
            'h2',
            { className: 'subheading', style: styles$1.confirmationSubheading },
            'You can now access all online assessments for ' + this.props.courseTitle + '.'
          ),
          React.createElement(
            'div',
            { className: 'confirmation-text-wrapper', style: styles$1.confirmationTextWrapper },
            React.createElement(
              'p',
              { style: styles$1.confirmationText },
              'Confirmation #' + this.props.confirmationNum
            ),
            React.createElement(
              'p',
              { style: styles$1.confirmationText },
              'A receipt has been sent to your email address at ' + this.props.userEmail + '.'
            ),
            React.createElement('br', null),
            React.createElement(
              'p',
              { style: styles$1.confirmationText },
              'The purchase will show up as Lumen Learning on your debit or credit card statement.'
            )
          ),
          React.createElement(
            'button',
            { style: styles$1.continueButton, onClick: this._handleClick },
            'Continue'
          )
        )
      );
    }
  }, {
    key: '_handleClick',
    value: function _handleClick() {
      window.location = this.props.redirectTo;
    }
  }]);
  return DirectPayConfirmation;
}(React.Component);

var styles$2 = {
  landingPageWrapper: {
    fontFamily: 'Libre Franklin, sans serif',
    margin: '2.5em 1.75em',
    display: 'flex',
    flexDirection: 'column',
    color: '#212b36'
  },
  headerText: {
    fontFamily: 'Libre Franklin, sans serif',
    fontSize: '14px',
    fontWeight: 'normal',
    fontStyle: 'normal',
    fontStretch: 'normal',
    lineHeight: '1.43',
    letterSpacing: 'normal',
    textAlign: 'left',
    color: '#212b36',
    marginLeft: '31px'
  },
  headerBox: {
    fontFamily: 'Libre Franklin, sans serif',
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'left',
    padding: '24px',
    marginLeft: '12px',
    marginRight: '12px',
    marginBottom: '24px',
    backgroundColor: 'rgba(252, 240, 205, 0.7)',
    boxShadow: '0 1px 3px 0 rgba(63, 63, 68, 0.15), 0 0 0 1px rgba(63, 63, 68, 0.05)'
  },
  lumenLogo: {
    height: '41px',
    marginLeft: '1em',
    width: '88.7px'
  },
  schoolLogo: {
    height: '100%',
    width: '100%',
    display: 'block'
  },
  schoolLogoWrapper: {
    // height: '69px',
    width: '224px',
    paddingBottom: '50px'
  },
  lumenAttributionWrapper: {
    display: 'flex',
    alignItems: 'center',
    fontSize: '12px',
    paddingLeft: '35px',
    paddingTop: '35px'
  },
  smallBlockHeaders: {
    width: '240px',
    height: '16px',
    fontSize: '12px',
    fontWeight: '600',
    fontStyle: 'normal',
    fontStretch: 'normal',
    lineHeight: '1.33',
    letterSpacing: 'normal',
    textAlign: 'left',
    color: '#212b36',
    marginTop: '0px',
    marginBottom: '0px',
    paddingBottom: '3px'
  }
};

function styleInject(css, ref) {
  if ( ref === void 0 ) ref = {};
  var insertAt = ref.insertAt;

  if (!css || typeof document === 'undefined') { return; }

  var head = document.head || document.getElementsByTagName('head')[0];
  var style = document.createElement('style');
  style.type = 'text/css';

  if (insertAt === 'top') {
    if (head.firstChild) {
      head.insertBefore(style, head.firstChild);
    } else {
      head.appendChild(style);
    }
  } else {
    head.appendChild(style);
  }

  if (style.styleSheet) {
    style.styleSheet.cssText = css;
  } else {
    style.appendChild(document.createTextNode(css));
  }
}

var css = "/* http://meyerweb.com/eric/tools/css/reset/\n   v2.0 | 20110126\n   License: none (public domain)\n*/\n\nhtml, body, div, span, applet, object, iframe,\nh1, h2, h3, h4, h5, h6, p, blockquote, pre,\na, abbr, acronym, address, big, cite, code,\ndel, dfn, em, img, ins, kbd, q, s, samp,\nsmall, strike, strong, sub, sup, tt, var,\nb, u, i, center,\ndl, dt, dd, ol, ul, li,\nfieldset, form, label, legend,\ntable, caption, tbody, tfoot, thead, tr, th, td,\narticle, aside, canvas, details, embed,\nfigure, figcaption, footer, header, hgroup,\nmenu, nav, output, ruby, section, summary,\ntime, mark, audio, video {\n\tmargin: 0;\n\tpadding-top: 0;\n\tpadding-bottom: 0;\n\tpadding-left: 0;\n\tpadding-right: 0;\n\tborder: 0;\n\tfont-family: 'Libre Franklin, sans serif';\n\tfont-size: 100%;\n\tfont: inherit;\n\tvertical-align: baseline;\n\tbox-sizing: unset;\n}\n/* HTML5 display-role reset for older browsers */\narticle, aside, details, figcaption, figure,\nfooter, header, hgroup, menu, nav, section {\n\tdisplay: block;\n}\nbody {\n\tline-height: 1 !important;\n}\nol, ul {\n\tlist-style: none;\n}\nblockquote, q {\n\tquotes: none;\n}\nblockquote:before, blockquote:after,\nq:before, q:after {\n\tcontent: '';\n\tcontent: none;\n}\ntable {\n\tborder-collapse: collapse;\n\tborder-spacing: 0;\n}\n\nbutton {\n\tmargin-bottom: 0;\n}\n";
styleInject(css);

var css$1 = "@import url('https://fonts.googleapis.com/css?family=Libre+Franklin:400,700');\n";
styleInject(css$1);

var DirectPayLandingPage = function (_React$Component) {
  inherits(DirectPayLandingPage, _React$Component);

  function DirectPayLandingPage(props) {
    classCallCheck(this, DirectPayLandingPage);
    return possibleConstructorReturn(this, (DirectPayLandingPage.__proto__ || Object.getPrototypeOf(DirectPayLandingPage)).call(this, props));
  }

  createClass(DirectPayLandingPage, [{
    key: "render",
    value: function render() {
      return React.createElement(
        "div",
        null,
        this._headerComponentDecision(),
        React.createElement(
          "div",
          { className: "landing-page-wrapper", style: styles$2.landingPageWrapper },
          React.createElement("img", {
            src: this.props.schoolLogoUrl,
            alt: this.props.institutionName + " logo",
            style: { width: '224px', height: '69px', objectFit: 'contain' }
          }),
          React.createElement(
            "div",
            { style: { paddingTop: '40px' } },
            this._loadCorrectView()
          ),
          React.createElement(
            "div",
            null,
            this.props.attributionLogoUrl === null ? "" : React.createElement(
              "div",
              { className: "lumen-attribution", style: styles$2.lumenAttributionWrapper },
              React.createElement(
                "span",
                null,
                "Open Courseware by "
              ),
              React.createElement("img", { src: "https://s3-us-west-2.amazonaws.com/lumen-components/assets/Lumen-300x138.png", alt: "Lumen Learning logo", className: "lumen-logo",
                style: styles$2.lumenLogo })
            )
          )
        )
      );
    }
  }, {
    key: "_loadCorrectView",
    value: function _loadCorrectView() {
      if (this.props.paymentStatus === 'has_access') {
        return React.createElement(DirectPayConfirmation, {
          chargeAmount: this.props.chargeAmount,
          confirmationNum: this.props.confirmationNum,
          userEmail: this.props.userEmail,
          courseTitle: this.props.courseTitle,
          redirectTo: this.props.redirectTo
        });
      } else {
        return React.createElement(DirectPayCourseActivation, {
          paymentStatus: this.props.paymentStatus,
          stripeKey: this.props.stripeKey,
          chargeAmount: this.props.chargeAmount,
          institutionName: this.props.institutionName,
          chargeDescription: this.props.chargeDescription,
          stripeModalLogoUrl: this.props.stripeModalLogoUrl,
          courseTitle: this.props.courseTitle,
          userEmail: this.props.userEmail,
          schoolLogoUrl: this.props.schoolLogoUrl,
          attributionLogoUrl: this.props.attributionLogoUrl,
          endpointUrl: this.props.endpointUrl,
          redirectTo: this.props.redirectTo,
          trialTimeRemaining: this.props.trialTimeRemaining
        });
      }
    }
  }, {
    key: "_renderCorrectHeaderText",
    value: function _renderCorrectHeaderText() {
      var language = void 0;

      if (this.props.paymentStatus === 'expired') {
        language = "Course content is still available. However, you need to pay to activate the assessments in this course.";
      } else if (this.props.paymentStatus === 'can_extend') {
        language = "Activate a one-time pass to extend your trial by 24 hours. ";
      } else {
        return '';
      }

      return language;
    }
  }, {
    key: "_headerComponentDecision",
    value: function _headerComponentDecision() {
      if (this.props.paymentStatus === 'expired' || this.props.paymentStatus === 'can_extend') {
        return React.createElement(
          "div",
          null,
          React.createElement("div", { style: { height: '3px', backgroundColor: '#edc200', marginLeft: '11px', marginRight: '11px' } }),
          React.createElement(
            "div",
            { className: "header-box", style: styles$2.headerBox },
            React.createElement(
              "div",
              { style: { display: 'flex', flexDirection: 'row' } },
              React.createElement("img", {
                src: "https://s3-us-west-2.amazonaws.com/lumen-components-prod/assets/icons/icon-warning.svg",
                alt: "warning icon",
                style: { paddingRight: '5px' }
              }),
              React.createElement(
                "div",
                { style: { alignContent: 'left', lineHeight: '1.9em', marginLeft: '5px' } },
                "Your Trial Has Expired"
              )
            ),
            React.createElement(
              "div",
              { className: "header-text", style: styles$2.headerText },
              this._renderCorrectHeaderText(),
              this.props.paymentStatus === 'can_extend' ? React.createElement(
                "a",
                { href: this.props.redirectTo, style: { textDecoration: 'underline' } },
                "Activate One-time Pass"
              ) : ""
            )
          )
        );
      }
    }
  }]);
  return DirectPayLandingPage;
}(React.Component);

var DirectPayAlertHeader = function (_React$Component) {
  inherits(DirectPayAlertHeader, _React$Component);

  function DirectPayAlertHeader() {
    classCallCheck(this, DirectPayAlertHeader);
    return possibleConstructorReturn(this, (DirectPayAlertHeader.__proto__ || Object.getPrototypeOf(DirectPayAlertHeader)).apply(this, arguments));
  }

  createClass(DirectPayAlertHeader, [{
    key: 'render',
    value: function render() {
      return React.createElement(
        'div',
        { className: true },
        React.createElement(
          'h3',
          { style: { color: 'purple' } },
          'HEADER ALERT'
        ),
        React.createElement(
          'h5',
          null,
          'A one-time low-cost activation of $30 is required for assessments. Course materials are always available.'
        ),
        React.createElement(
          'button',
          { onClick: function onClick() {
              return alert('Thank you page');
            } },
          'Pay Now'
        )
      );
    }
  }]);
  return DirectPayAlertHeader;
}(React.Component);

var direct_pay_components = {
  renderDirectPayLandingPage: function renderDirectPayLandingPage(elementId, props) {
    React.render(React.createElement(DirectPayLandingPage, props), document.getElementById(elementId));
  },
  renderDirectPayCourseActivation: function renderDirectPayCourseActivation(elementId, props) {
    React.render(React.createElement(DirectPayCourseActivation, props), document.getElementById(elementId));
  },
  renderDirectPayButton: function renderDirectPayButton(elementId, props) {
    React.render(React.createElement(DirectPayButton, props), document.getElementById(elementId));
  },
  renderDirectPayConfirmation: function renderDirectPayConfirmation(elementId, props) {
    React.render(React.createElement(DirectPayConfirmation, props), document.getElementById(elementId));
  },
  renderDirectPayHeaderAlert: function renderDirectPayHeaderAlert(elementId, props) {
    React.render(React.createElement(DirectPayAlertHeader, props), document.getElementById(elementId));
  }
};

return direct_pay_components;

}(React));
