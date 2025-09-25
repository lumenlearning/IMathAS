var directPayComponents = (function (React) {
  'use strict';

  function _interopNamespaceDefault(e) {
    var n = Object.create(null);
    if (e) {
      Object.keys(e).forEach(function (k) {
        if (k !== 'default') {
          var d = Object.getOwnPropertyDescriptor(e, k);
          Object.defineProperty(n, k, d.get ? d : {
            enumerable: true,
            get: function () { return e[k]; }
          });
        }
      });
    }
    n.default = e;
    return Object.freeze(n);
  }

  var React__namespace = /*#__PURE__*/_interopNamespaceDefault(React);

  var styles$7 = {
    parentWrapper: {
      display: 'block',
      paddingLeft: '35px'
    },
    leftColumn: {
      display: 'inline-block',
      paddingRight: '64px',
      maxWidth: '360px',
      verticalAlign: 'top'
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
      display: 'inline-block',
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
    },
    payNowButton: {
      fontSize: '14px',
      width: '90px',
      height: '36px',
      color: '#fff',
      backgroundColor: '#1e74d1',
      border: '1px solid #1064c0',
      borderRadius: '3px',
      margin: '13px 0',
      cursor: 'pointer',
      padding: 0
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

  var css_248z$2 = "#payment-button-form .stripe-button-el { \n    background-color: #1e74d1;\n    background-image: unset;\n    border: 1px solid #004c9f;\n    border-radius: 3px;\n    box-shadow: unset;\n    color: #fff;\n    font-size: 14px;\n    height: 36px;\n    width: 106px;\n    padding: 0;\n    cursor: pointer;\n}\n\n#payment-button-form .stripe-button-el span {\n    background-image: unset;\n    border-radius: unset; \n    box-shadow: unset;\n    background-color: unset; \n    font-size: unset; \n    font-weight: unset;\n    font-family: unset;\n    text-shadow: unset;\n}";
  styleInject(css_248z$2);

  class DirectPayButton extends React__namespace.Component {
    constructor(props) {
      super(props);
      this._openCheckout = this._openCheckout.bind(this);
    }
    componentDidMount() {
      let scriptEl = document.createElement('script');
      scriptEl.setAttribute('src', 'https://checkout.stripe.com/checkout.js');
      scriptEl.setAttribute('class', 'stripe-button');
      scriptEl.setAttribute('id', 'strip-button');
      scriptEl.setAttribute('data-key', this.props.stripeKey);
      scriptEl.setAttribute('data-amount', this.props.chargeAmount);
      scriptEl.setAttribute('data-name', 'Lumen Learning');
      scriptEl.setAttribute('data-description', this.props.chargeDescription);
      scriptEl.setAttribute('data-image', this.props.stripeModalLogoUrl);
      scriptEl.setAttribute('data-locale', 'auto');
      scriptEl.setAttribute('data-allow-remember-me', 'false');
      scriptEl.setAttribute('data-label', 'Pay Now');
      document.getElementById('payment-button-form').appendChild(scriptEl);
    }
    render() {
      return /*#__PURE__*/React__namespace.createElement("div", {
        id: "payment-button-container",
        className: "form-control",
        style: {
          paddingBottom: '13px',
          paddingTop: '13px'
        }
      }, /*#__PURE__*/React__namespace.createElement("form", {
        id: "payment-button-form",
        action: this.props.endpointUrl,
        onClick: this._openCheckout,
        method: "POST"
      }, /*#__PURE__*/React__namespace.createElement("input", {
        type: "hidden",
        id: "zipcode",
        name: "zipcode",
        value: this.props.zipcode
      })));
    }
    _openCheckout() {
      return {
        image: this.props.image,
        name: this.props.institutionName,
        description: this.props.chargeDescription,
        amount: this.props.chargeAmount
      };
    }
  }

  // TODO change pay button text?  How much do we care about specific words? Currently shows as 'Pay with Card' (comes from Stripe)
  // TODO Have Checkout collect user's ZIP to help reduce fraud. Add data-zip-code="true" to the above and make use of Radar's built-in rules to decline payments that fail verification.
  // added, but still need to change rules when in live mode (https://dashboard.stripe.com/radar/rules)
  // TODO prevent potential popup blocker for Checkout - https://stripe.com/docs/checkout#how-do-i-prevent-the-checkout-popup-from-being-blocked
  //  added the openCheckout function, but can't tell that it ever gets blocked anyway even with installation of a popup blocker.  Checkout docs said it may
  // get blocked on certain mobile devices and with Internet Explorer

  class DirectPayCourseActivation extends React__namespace.Component {
    constructor(props) {
      super(props);
      this.state = {
        windowWidth: null,
        showCheckout: false
      };
      this._handleWindowResize = this._handleWindowResize.bind(this);
      this._toggleCheckout = this._toggleCheckout.bind(this);
    }
    componentWillMount() {
      this.setState({
        windowWidth: this._getWindowSize()
      });
      window.addEventListener("resize", this._handleWindowResize);
    }
    render() {
      let termsOfServiceURL = "https://lumenlearning.com/policies/terms-of-service/";
      let privacyPolicy = "https://lumenlearning.com/policies/privacy-policy/";
      let priceInDollars = (this.props.chargeAmount / 100).toLocaleString('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2
      });
      return /*#__PURE__*/React__namespace.createElement("div", null, /*#__PURE__*/React__namespace.createElement("div", {
        style: {
          ...styles$7.parentWrapper,
          ...this._getParentStyle()
        }
      }, /*#__PURE__*/React__namespace.createElement("div", {
        className: "left-column",
        style: {
          ...styles$7.leftColumn,
          ...this._getTabletSpecs()
        }
      }, /*#__PURE__*/React__namespace.createElement("h1", {
        className: "activation-heading",
        style: styles$7.activationHeading
      }, this._headerLanguage()), /*#__PURE__*/React__namespace.createElement("h3", {
        className: "course-title",
        style: styles$7.courseTitle
      }, `${this.props.courseTitle}: ${priceInDollars}`), window.innerWidth < 800 ? /*#__PURE__*/React__namespace.createElement("p", {
        className: "top-small-block-text",
        style: styles$7.topSmallBlockText
      }, "This low-cost activation is only required for assessments. Course content is always available.") : "", /*#__PURE__*/React__namespace.createElement("p", {
        className: "terms-and-privacy",
        style: styles$7.termsAndPrivacy
      }, "By clicking on Pay Now or by starting a trial you agree to the Lumen Learning ", /*#__PURE__*/React__namespace.createElement("a", {
        href: termsOfServiceURL,
        target: "_blank",
        style: {
          textDecoration: 'underline',
          color: '#212b36'
        }
      }, "Terms of Service"), " and ", /*#__PURE__*/React__namespace.createElement("a", {
        href: privacyPolicy,
        target: "_blank",
        style: {
          textDecoration: 'underline',
          color: '#212b36'
        }
      }, "Privacy Policy"), "."), /*#__PURE__*/React__namespace.createElement(DirectPayButton, {
        style: styles$7.payNowButton,
        paymentStatus: this.props.paymentStatus,
        stripeKey: this.props.stripeKey,
        chargeAmount: this.state.total,
        institutionName: this.props.institutionName,
        chargeDescription: this.props.chargeDescription,
        stripeModalLogoUrl: this.props.stripeModalLogoUrl,
        endpointUrl: this.props.endpointUrl,
        userEmail: this.props.userEmail,
        zipcode: this.state.zipcode
      })), this._renderRightColumn()));
    }
    componentWillUnmount() {
      window.removeEventListener("resize");
    }
    _toggleCheckout() {
      let showCheckout = this.state.showCheckout;
      this.setState({
        showCheckout: !showCheckout
      });
    }
    _renderRightColumn() {
      if (window.innerWidth < 800) {
        return this._renderSmallBlockDecision();
      } else {
        return /*#__PURE__*/React__namespace.createElement("div", {
          className: "right-column",
          style: styles$7.rightColumn
        }, /*#__PURE__*/React__namespace.createElement("div", {
          style: {
            display: 'flex',
            flexDirection: 'row'
          }
        }, /*#__PURE__*/React__namespace.createElement("img", {
          src: "https://s3-us-west-2.amazonaws.com/lumen-components-prod/assets/icons/icon-info.svg",
          alt: "information icon",
          style: {
            width: '24',
            paddingRight: '5px',
            objectFit: 'contain'
          }
        }), /*#__PURE__*/React__namespace.createElement("p", {
          className: "small-block-headers",
          style: styles$7.smallBlockHeaders
        }, "GOOD TO KNOW!")), /*#__PURE__*/React__namespace.createElement("p", {
          className: "top-small-block-text",
          style: styles$7.topSmallBlockText
        }, "This low-cost activation is only required for assessments. Course content is always available."), this._renderSmallBlockDecision());
      }
    }
    _renderSmallBlockDecision() {
      let renderStatusBasedText = this._renderStatusBasedText();
      let redirectTo = this.props.redirectTo;
      if (this.props.paymentStatus === 'in_trial' || this.props.paymentStatus === 'trial_not_started') {
        return /*#__PURE__*/React__namespace.createElement("div", null, /*#__PURE__*/React__namespace.createElement("p", {
          className: "bottom-small-block-headers",
          style: styles$7.bottomSmallBlockHeaders
        }, renderStatusBasedText.smallHeader), /*#__PURE__*/React__namespace.createElement("p", {
          className: "bottom-small-block-text",
          style: styles$7.bottomSmallBlockText
        }, renderStatusBasedText.smallText), /*#__PURE__*/React__namespace.createElement("a", {
          href: redirectTo,
          className: "two-week-trial-text",
          style: styles$7.twoWeekTrialText
        }, renderStatusBasedText.smallLink));
      }
    }
    _renderStatusBasedText() {
      if (this.props.paymentStatus === 'in_trial') {
        return {
          smallHeader: "CONTINUE TRIAL",
          smallText: this._trialContinueLanguage(),
          smallLink: this.props.trialType === "quiz_count" ? "Use Pass" : "Continue Trial"
        };
      } else if (this.props.paymentStatus === 'trial_not_started') {
        return {
          smallHeader: "NOT READY TO PAY?",
          smallText: this._trialStartLanguage(),
          smallLink: this.props.trialType === "quiz_count" ? "Use Pass" : "Start Trial"
        };
      }
    }
    _trialStartLanguage() {
      if (this.props.trialType === "quiz_count") {
        return "You can access up to two assessments before activation is required. 2 of 2 passes available.";
      }
      return "You can access your assessments for two weeks before activation is required.";
    }
    _trialContinueLanguage() {
      if (this.props.trialType === "quiz_count") {
        let trialPassesRemaining = this.props.trialPassesRemaining;
        return "You can access up to two assessments before activation is required. " + trialPassesRemaining + " of 2 passes available.";
      } else {
        let trialTimeRemaining = this._getTrialTimeRemainingWords();
        return "You can continue to access your assessments for " + trialTimeRemaining + " before activation is required.";
      }
    }
    _headerLanguage() {
      let language = "Course Assessment Activation";
      if (this.props.schoolLogoUrl != null && this.props.schoolLogoUrl !== '') {
        language = "Assessment Activation";
      }
      return language;
    }
    _getTrialTimeRemainingWords() {
      let timeLeft = this.props.trialTimeRemaining;
      if (60 > timeLeft) {
        timeLeft = 'less than 1 minute';
      } else if (60 < timeLeft && 120 > timeLeft) {
        timeLeft = '1 minute';
      } else if (3600 >= timeLeft) {
        timeLeft = `${Math.floor(timeLeft / 60)} minutes`;
      } else if (3600 <= timeLeft && 7200 > timeLeft) {
        timeLeft = `${Math.floor(timeLeft / 3600)} hour`;
      } else if (86400 > timeLeft) {
        timeLeft = `${Math.floor(timeLeft / 3600)} hours`;
      } else if (86400 < timeLeft && 172800 > timeLeft) {
        timeLeft = '1 day';
      } else {
        timeLeft = `${(timeLeft / 86400).toFixed()} days`;
      }
      return timeLeft;
    }
    _getWindowSize() {
      return window.innerWidth;
    }
    _handleWindowResize(e) {
      this.setState({
        windowWidth: this._getWindowSize()
      });
    }
    _getParentStyle() {
      let styles = {};
      if (this.state.windowWidth < 800) {
        styles = {
          flexDirection: 'column'
        };
      }
      return styles;
    }
    _getTabletSpecs() {
      let styles = {};
      if (this.state.windowWidth < 800 && this.state.windowWidth > 414) {
        styles = {
          width: '340px'
        };
      }
      return styles;
    }
  }

  var styles$6 = {
    confirmationPageWrapper: {
      fontFamily: 'Libre Franklin, sans serif',
      margin: '2.5em 2em',
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
    confirmationDetailKey: {
      fontSize: '1.2em',
      fontWeight: 'bold'
    },
    confirmationDetailValue: {
      fontSize: '1.2em',
      marginBottom: '0.2em'
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
    },
    logoImg: {
      width: '224px',
      height: '69px',
      objectFit: 'contain',
      marginBottom: '1.75em'
    }
  };

  class DirectPayConfirmation extends React__namespace.Component {
    constructor(props) {
      super(props);
      this._handleClick = this._handleClick.bind(this);
    }
    render() {
      let date = new Date();
      let timestamp = date.toLocaleString();
      if (this.props.confirmationNum != undefined) {
        return /*#__PURE__*/React__namespace.createElement("div", {
          style: styles$6.confirmationPageWrapper
        }, /*#__PURE__*/React__namespace.createElement("div", {
          className: "confirmation-wrapper",
          style: styles$6.confirmationWrapper
        }, /*#__PURE__*/React__namespace.createElement("h1", {
          className: "heading",
          style: styles$6.confirmationHeading
        }, "Thank You!"), /*#__PURE__*/React__namespace.createElement("h2", {
          className: "subheading",
          style: styles$6.confirmationSubheading
        }, `You can now access all online assessments for ${this.props.courseTitle}.`), /*#__PURE__*/React__namespace.createElement("div", {
          className: "confirmation-text-wrapper",
          style: styles$6.confirmationTextWrapper
        }, /*#__PURE__*/React__namespace.createElement("p", {
          style: styles$6.confirmationText
        }, `Confirmation #${this.props.confirmationNum}`), /*#__PURE__*/React__namespace.createElement("p", {
          style: styles$6.confirmationText
        }, `A receipt has been sent to your email address at ${this.props.userEmail}.`), /*#__PURE__*/React__namespace.createElement("br", null), /*#__PURE__*/React__namespace.createElement("p", {
          style: styles$6.confirmationText
        }, "The purchase will show up as Lumen Learning on your debit or credit card statement.")), /*#__PURE__*/React__namespace.createElement("button", {
          style: styles$6.continueButton,
          onClick: this._handleClick
        }, "Continue")));
      } else {
        return /*#__PURE__*/React__namespace.createElement("div", {
          style: styles$6.confirmationPageWrapper
        }, /*#__PURE__*/React__namespace.createElement("div", {
          className: "confirmation-wrapper",
          style: styles$6.confirmationWrapper
        }, /*#__PURE__*/React__namespace.createElement("h1", {
          className: "heading",
          style: styles$6.confirmationHeading
        }, "You're all set!"), /*#__PURE__*/React__namespace.createElement("h2", {
          className: "subheading",
          style: styles$6.confirmationSubheading
        }, "Thank you for submitting your Lumen course activation code. Please print this screen or save it as a PDF for your records."), /*#__PURE__*/React__namespace.createElement("div", {
          className: "confirmation-text-wrapper",
          style: styles$6.confirmationTextWrapper
        }, /*#__PURE__*/React__namespace.createElement("p", {
          style: styles$6.confirmationDetailValue
        }, /*#__PURE__*/React__namespace.createElement("span", {
          style: styles$6.confirmationDetailKey
        }, "Student Name: "), " ", this.props.studentName, " "), /*#__PURE__*/React__namespace.createElement("p", {
          style: styles$6.confirmationDetailValue
        }, /*#__PURE__*/React__namespace.createElement("span", {
          style: styles$6.confirmationDetailKey
        }, "Course Name: "), " ", this.props.courseTitle, " "), /*#__PURE__*/React__namespace.createElement("p", {
          style: styles$6.confirmationDetailValue
        }, /*#__PURE__*/React__namespace.createElement("span", {
          style: styles$6.confirmationDetailKey
        }, "Activation Code Used: "), " ", this.props.activationCode.toUpperCase()), /*#__PURE__*/React__namespace.createElement("p", {
          style: styles$6.confirmationDetailValue
        }, /*#__PURE__*/React__namespace.createElement("span", {
          style: styles$6.confirmationDetailKey
        }, "Timestamp: "), " ", timestamp, " ")), /*#__PURE__*/React__namespace.createElement("button", {
          style: styles$6.continueButton,
          onClick: this._handleClick
        }, "Continue"), this._renderFooterLogo()));
      }
    }
    _handleClick() {
      window.location = this.props.redirectTo;
    }
    _getLogoUrl() {
      return this.props.schoolLogoUrl || 'https://s3-us-west-2.amazonaws.com/lumen-platform-assets/images/lumen-open-courseware.png';
    }
    _getLogoAltText() {
      return this.props.institutionName ? `${this.props.institutionName} logo` : 'Lumen Open Courseware logo';
    }
    _renderFooterLogo() {
      if (this.props.schoolLogoUrl != null && this.props.schoolLogoUrl !== '') {
        return /*#__PURE__*/React__namespace.createElement("div", {
          className: "lumen-attribution",
          style: styles$6.lumenAttributionWrapper
        }, /*#__PURE__*/React__namespace.createElement("span", null, "Open Courseware by "), /*#__PURE__*/React__namespace.createElement("a", {
          style: styles$6.lumenLogoLink,
          href: "https://www.lumenlearning.com",
          target: "_blank"
        }, /*#__PURE__*/React__namespace.createElement("img", {
          src: "https://s3-us-west-2.amazonaws.com/lumen-components/assets/Lumen-300x138.png",
          alt: "Lumen Learning logo",
          className: "lumen-logo",
          style: styles$6.lumenLogo
        })));
      }
    }
  }

  var styles$5 = {
    landingPageWrapper: {
      fontFamily: 'Libre Franklin, sans serif',
      margin: '2.5em 1.75em',
      display: 'flex',
      flexDirection: 'column',
      color: '#212b36'
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

  var css_248z$1 = "/* http://meyerweb.com/eric/tools/css/reset/\n   v2.0 | 20110126\n   License: none (public domain)\n*/\n\nhtml, body, div, span, applet, object, iframe,\nh1, h2, h3, h4, h5, h6, p, blockquote, pre,\na, abbr, acronym, address, big, cite, code,\ndel, dfn, em, img, ins, kbd, q, s, samp,\nsmall, strike, strong, sub, sup, tt, var,\nb, u, i, center,\ndl, dt, dd, ol, ul, li,\nfieldset, form, label, legend,\ntable, caption, tbody, tfoot, thead, tr, th, td,\narticle, aside, canvas, details, embed,\nfigure, figcaption, footer, header, hgroup,\nmenu, nav, output, ruby, section, summary,\ntime, mark, audio, video {\n\tmargin: 0;\n\tpadding-top: 0;\n\tpadding-bottom: 0;\n\tpadding-left: 0;\n\tpadding-right: 0;\n\tborder: 0;\n\tfont-family: 'Libre Franklin, sans serif';\n\tfont-size: 100%;\n\tfont: inherit;\n\tvertical-align: baseline;\n\tbox-sizing: unset;\n}\n\n/* HTML5 display-role reset for older browsers */\n\narticle, aside, details, figcaption, figure,\nfooter, header, hgroup, menu, nav, section {\n\tdisplay: block;\n}\n\nbody {\n\tline-height: 1 !important;\n}\n\nol, ul {\n\tlist-style: none;\n}\n\nblockquote, q {\n\tquotes: none;\n}\n\nblockquote:before, blockquote:after,\nq:before, q:after {\n\tcontent: '';\n\tcontent: none;\n}\n\ntable {\n\tborder-collapse: collapse;\n\tborder-spacing: 0;\n}\n\nbutton {\n\tmargin-bottom: 0;\n}\n";
  styleInject(css_248z$1);

  var css_248z = "@import url('https://fonts.googleapis.com/css?family=Libre+Franklin:400,700');\n";
  styleInject(css_248z);

  class DirectPayLandingPage extends React__namespace.Component {
    constructor(props) {
      super(props);
    }
    render() {
      return /*#__PURE__*/React__namespace.createElement("div", null, this._headerComponentDecision(), /*#__PURE__*/React__namespace.createElement("div", {
        className: "landing-page-wrapper",
        style: styles$5.landingPageWrapper
      }, /*#__PURE__*/React__namespace.createElement("img", {
        src: this._getSchoolLogoUrl(),
        alt: `${this.props.institutionName} logo`,
        style: {
          width: '224px',
          height: '69px',
          objectFit: 'contain'
        }
      }), /*#__PURE__*/React__namespace.createElement("div", {
        style: {
          paddingTop: '40px'
        }
      }, this._loadCorrectView()), this._renderFooterLogo()));
    }
    _getSchoolLogoUrl() {
      //return this.props.schoolLogoUrl || 'https://s3-us-west-2.amazonaws.com/lumen-platform-assets/images/lumen-open-courseware.png';
      return this.props.schoolLogoUrl || 'https://content-cdn.one.lumenlearning.com/wp-content/uploads/2023/09/20212456/lumen-primary-logo.png';
    }
    _loadCorrectView() {
      if (this.props.paymentStatus === 'has_access') {
        return /*#__PURE__*/React__namespace.createElement(DirectPayConfirmation, {
          chargeAmount: this.props.chargeAmount,
          confirmationNum: this.props.confirmationNum,
          userEmail: this.props.userEmail,
          courseTitle: this.props.courseTitle,
          redirectTo: this.props.redirectTo,
          studentName: this.props.studentName,
          activationCode: this.props.activationCode,
          timestamp: this.props.timestamp
        });
      } else {
        return /*#__PURE__*/React__namespace.createElement(DirectPayCourseActivation, {
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
          trialTimeRemaining: this.props.trialTimeRemaining,
          trialPassesRemaining: this.props.trialPassesRemaining,
          trialType: this.props.trialType
        });
      }
    }
    _renderFooterLogo() {
      if (this.props.schoolLogoUrl != null && this.props.schoolLogoUrl !== '') {
        return /*#__PURE__*/React__namespace.createElement("div", {
          className: "lumen-attribution",
          style: styles$5.lumenAttributionWrapper
        }, /*#__PURE__*/React__namespace.createElement("span", null, "Open Courseware by "), /*#__PURE__*/React__namespace.createElement("a", {
          href: "https://www.lumenlearning.com",
          target: "_blank"
        }, /*#__PURE__*/React__namespace.createElement("img", {
          src: "https://s3-us-west-2.amazonaws.com/lumen-components/assets/Lumen-300x138.png",
          alt: "Lumen Learning logo",
          className: "lumen-logo",
          style: styles$5.lumenLogo
        })));
      }
    }
    _renderCorrectHeaderText() {
      let language;
      if (this.props.paymentStatus === 'expired') {
        language = " Course content is still available. However, you need to pay to activate the assessments in this course. ";
      } else if (this.props.paymentStatus === 'can_extend') {
        language = this._extendLanguage();
      } else {
        return '';
      }
      return language;
    }
    _extendLanguage() {
      if (this.props.trialType === "quiz_count") {
        return " Use a final one-time pass to access this assessment. ";
      } else {
        return " Activate a one-time pass to extend your trial by 24 hours. ";
      }
    }
    _renderLinkText() {
      return this.props.trialType === "quiz_count" ? "Use Assessment Pass" : "Activate One-time Pass";
    }
    _headerComponentDecision() {
      if (this.props.paymentStatus === 'expired' || this.props.paymentStatus === 'can_extend') {
        return /*#__PURE__*/React__namespace.createElement("div", null, /*#__PURE__*/React__namespace.createElement("div", {
          style: {
            height: '3px',
            backgroundColor: '#edc200',
            marginLeft: '11px',
            marginRight: '11px'
          }
        }), /*#__PURE__*/React__namespace.createElement("div", {
          className: "header-box",
          style: styles$5.headerBox
        }, /*#__PURE__*/React__namespace.createElement("div", {
          style: {
            display: 'flex',
            flexDirection: 'row'
          }
        }, /*#__PURE__*/React__namespace.createElement("img", {
          src: "https://s3-us-west-2.amazonaws.com/lumen-components-prod/assets/icons/icon-warning.svg",
          alt: "warning icon",
          style: {
            paddingRight: '5px'
          }
        }), /*#__PURE__*/React__namespace.createElement("div", {
          style: {
            alignContent: 'left',
            lineHeight: '1.2em',
            marginLeft: '5px'
          }
        }, /*#__PURE__*/React__namespace.createElement("p", {
          style: {
            fontSize: '14px'
          }
        }, this.props.trialType === "quiz_count" ? "You've run out of activation passes." : "Your Trial Has Expired.", /*#__PURE__*/React__namespace.createElement("span", {
          className: "header-text"
        }, this._renderCorrectHeaderText(), this.props.paymentStatus === 'can_extend' ? /*#__PURE__*/React__namespace.createElement("a", {
          href: this.props.redirectTo,
          style: {
            textDecoration: 'underline',
            color: '#1e74d1'
          }
        }, this._renderLinkText()) : ""))))));
      }
    }
  }

  var styles$4 = {
    errorMessageWrapper: {
      display: 'block',
      marginTop: '4px'
    },
    taxPageWrapper: {
      maxWidth: '278px',
      marginLeft: '40px'
    },
    heading: {
      fontSize: '32px',
      fontWeight: 'bold',
      lineHeight: 1.23,
      color: '#212b36',
      marginBottom: '40px'
    },
    subHeading: {
      fontSize: '14px',
      fontWeight: 'bold',
      color: '#212b36',
      marginBottom: '14px'
    },
    zipcodeLabel: {
      display: 'block',
      marginBottom: '6px',
      fontSize: '14px'
    },
    zipcodeInput: {
      display: 'block',
      width: '262px',
      height: '36px',
      borderRadius: '3px',
      backgroundColor: '#fff',
      border: 'solid 1px #c4cdd5',
      fontSize: '16px',
      padding: '0 6px',
      marginBottom: '40px'
    },
    zipcodeInputError: {
      display: 'block',
      width: '262px',
      height: '36px',
      borderRadius: '3px',
      backgroundColor: '#fff',
      border: 'solid 1px #bf0711',
      fontSize: '16px',
      padding: '0 6px',
      marginBottom: '40px'
    },
    zipcodeError: {
      fontSize: '14px',
      color: '#bf0711',
      display: 'inline-block',
      verticalAlign: 'middle',
      marginLeft: '4px'
    },
    errorIcon: {
      width: '20px',
      height: '20px',
      display: 'inline-block',
      verticalAlign: 'middle'
    },
    table: {
      maxWidth: '278px',
      marginBottom: '47px'
    },
    tableHead: {
      fontSize: '14px',
      fontWeight: 'bold',
      borderBottom: 'solid 1px #ebecf0',
      paddingBottom: '10px',
      marginBottom: '10px'
    },
    columnOne: {
      display: 'table',
      fontSize: '14px',
      width: '278px',
      marginBottom: '12px'
    },
    assessmentActivation: {
      display: 'table-cell',
      textAlign: 'left'
    },
    preTaxValue: {
      display: 'table-cell',
      textAlign: 'right'
    },
    columnTwo: {
      display: 'table',
      fontSize: '14px',
      width: '278px',
      borderBottom: 'solid 1px #ebecf0',
      paddingBottom: '10px',
      marginBottom: '10px'
    },
    taxes: {
      display: 'table-cell',
      textAlign: 'left'
    },
    taxValueToCollect: {
      display: 'table-cell',
      textAlign: 'right'
    },
    columnThree: {
      display: 'table',
      fontSize: '14px',
      width: '278px'
    },
    total: {
      display: 'table-cell',
      textAlign: 'left'
    },
    totalValueToCollect: {
      display: 'table-cell',
      textAlign: 'right'
    },
    payNow: {
      fontSize: '14px',
      float: 'right',
      color: '#fff',
      width: '90px',
      height: '36px',
      borderRadius: '3px',
      backgroundColor: '#8eb9e7',
      border: 'solid 1px #7ba6d6',
      cursor: 'pointer'
    },
    payNowDisabled: {
      color: '#fff',
      width: '90px',
      height: '36px',
      background: 'linear-gradient(#a0d4f3,#44abe7 85%,#64b9eb)',
      borderRadius: '5px',
      fontSize: '14px',
      fontWeight: 500,
      cursor: 'not-allowed',
      margin: '13px 0',
      padding: 0
    },
    lumenAttributionWrapper: {
      display: 'flex',
      alignItems: 'center',
      fontSize: '12px',
      marginTop: '4em'
    },
    lumenLogo: {
      height: '41px',
      marginLeft: '1em',
      width: '88.7px'
    },
    lumenLogoLink: {
      height: '41px'
    }
  };

  class CheckoutTaxPage extends React__namespace.Component {
    constructor(props) {
      super(props);
      this.state = {
        zipcode: '',
        taxAmount: '-',
        total: '-',
        errors: []
      };
      this._setZipCode = this._setZipCode.bind(this);
    }
    render() {
      return /*#__PURE__*/React__namespace.createElement("div", {
        style: styles$4.taxPageWrapper
      }, /*#__PURE__*/React__namespace.createElement("h1", {
        style: styles$4.heading
      }, "Checkout"), /*#__PURE__*/React__namespace.createElement("h2", {
        style: styles$4.subHeading
      }, "Calculate Taxes"), /*#__PURE__*/React__namespace.createElement("label", {
        htmlFor: "zipcode",
        style: styles$4.zipcodeLabel
      }, "Enter your 5-digit zip code"), /*#__PURE__*/React__namespace.createElement("input", {
        type: "text",
        id: "zipcode",
        name: "zipcode",
        style: this.state.errors.length > 0 ? styles$4.zipcodeInputError : styles$4.zipcodeInput,
        onChange: this._setZipCode,
        maxLength: "5"
      }), /*#__PURE__*/React__namespace.createElement("div", {
        style: styles$4.table
      }, /*#__PURE__*/React__namespace.createElement("div", {
        style: styles$4.tableHead
      }, "Cost Summary"), /*#__PURE__*/React__namespace.createElement("div", {
        style: styles$4.columnOne
      }, /*#__PURE__*/React__namespace.createElement("div", {
        style: styles$4.assessmentActivation
      }, "Assessment Activation"), /*#__PURE__*/React__namespace.createElement("div", {
        style: styles$4.preTaxValue
      }, this._convertToDollars(this.props.amount_in_cents))), /*#__PURE__*/React__namespace.createElement("div", {
        style: styles$4.columnTwo
      }, /*#__PURE__*/React__namespace.createElement("div", {
        style: styles$4.taxes
      }, "Taxes"), /*#__PURE__*/React__namespace.createElement("div", {
        style: styles$4.taxValueToCollect
      }, this._convertToDollars(this.state.taxAmount))), /*#__PURE__*/React__namespace.createElement("div", {
        style: styles$4.columnThree
      }, /*#__PURE__*/React__namespace.createElement("div", {
        style: styles$4.total
      }, "Total"), /*#__PURE__*/React__namespace.createElement("div", {
        style: styles$4.totalValueToCollect
      }, this._convertToDollars(this.state.total))), this._renderErrors()), this._renderPayButton(), this._renderFooterLogo());
    }
    _renderErrors() {
      if (this.state.errors.length > 0) {
        return /*#__PURE__*/React__namespace.createElement("div", {
          style: styles$4.errorMessageWrapper
        }, /*#__PURE__*/React__namespace.createElement("img", {
          style: styles$4.errorIcon,
          src: "https://s3-us-west-2.amazonaws.com/lumen-components-prod/assets/icons/icon-polaris-warning.png",
          alt: "warning icon"
        }), /*#__PURE__*/React__namespace.createElement("p", {
          style: styles$4.zipcodeError
        }, this.state.errors.map(error => {
          return /*#__PURE__*/React__namespace.createElement("p", {
            style: styles$4.zipcodeError
          }, error);
        })));
      }
    }
    _renderFooterLogo() {
      if (this.props.schoolLogoUrl != null && this.props.schoolLogoUrl !== '') {
        return /*#__PURE__*/React__namespace.createElement("div", {
          className: "lumen-attribution",
          style: styles$4.lumenAttributionWrapper
        }, /*#__PURE__*/React__namespace.createElement("span", null, "Open Courseware by "), /*#__PURE__*/React__namespace.createElement("a", {
          style: styles$4.lumenLogoLink,
          href: "https://www.lumenlearning.com",
          target: "_blank"
        }, /*#__PURE__*/React__namespace.createElement("img", {
          src: "https://s3-us-west-2.amazonaws.com/lumen-components/assets/Lumen-300x138.png",
          alt: "Lumen Learning logo",
          className: "lumen-logo",
          style: styles$4.lumenLogo
        })));
      }
    }
    _renderPayButton() {
      if ('-' != this.state.total) {
        return /*#__PURE__*/React__namespace.createElement(DirectPayButton, {
          paymentStatus: this.props.paymentStatus,
          stripeKey: this.props.stripeKey,
          chargeAmount: this.state.total,
          institutionName: this.props.institutionName,
          chargeDescription: this.props.chargeDescription,
          stripeModalLogoUrl: this.props.stripeModalLogoUrl,
          endpointUrl: this.props.endpointUrl,
          userEmail: this.props.userEmail,
          zipcode: this.state.zipcode
        });
      } else {
        return /*#__PURE__*/React__namespace.createElement("button", {
          style: styles$4.payNowDisabled,
          disabled: "true"
        }, "Pay Now");
      }
    }
    _setZipCode(e) {
      this.setState({
        taxAmount: '-',
        total: '-'
      });
      if (e.target.value.length === 5) {
        this._getTaxAmount(e.target.value);
      }
    }
    _convertToDollars(amount_in_cents) {
      if (amount_in_cents === '-') {
        return '-';
      } else {
        return (amount_in_cents / 100).toLocaleString('en-US', {
          style: 'currency',
          currency: 'USD',
          minimumFractionDigits: 2
        });
      }
    }
    _getTaxAmount(zipcode) {
      let data = {
        amount_in_cents: parseInt(this.props.amount_in_cents, 10),
        zipcode: zipcode
      };
      fetch(this._getTaxApiUrl(), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
      }).then(res => {
        let promise = res.json();
        promise.then(value => {
          if (value.errors != undefined) {
            this.setState({
              taxAmount: '-',
              total: '-',
              errors: value.errors
            });
          } else {
            this.setState({
              taxAmount: value.tax_amount_in_cents,
              total: value.tax_amount_in_cents + parseInt(this.props.amount_in_cents, 10),
              errors: [],
              zipcode: zipcode
            });
          }
        });
      });
    }
    _getTaxApiUrl() {
      if (window.location.host.indexOf('ludev.team') !== -1) {
        return 'https://admin.ludev.team/api/student_pay/tax';
      } else {
        return 'https://admin.lumenlearning.com/api/student_pay/tax';
      }
    }
  }

  class DirectPayAlertHeader extends React__namespace.Component {
    render() {
      return /*#__PURE__*/React__namespace.createElement("div", {
        className: true
      }, /*#__PURE__*/React__namespace.createElement("h3", {
        style: {
          color: 'purple'
        }
      }, "HEADER ALERT"), /*#__PURE__*/React__namespace.createElement("h5", null, "A one-time low-cost activation of $30 is required for assessments. Course materials are always available."), /*#__PURE__*/React__namespace.createElement("button", {
        onClick: () => alert('Thank you page')
      }, "Pay Now"));
    }
  }

  var styles$3 = {
    pageWrapper: {
      lineHeight: '24px'
    },
    pageWrapperInner: {
      fontFamily: 'Libre Franklin, sans serif',
      margin: '2.5em 2em',
      display: 'flex',
      flexDirection: 'column',
      color: '#212b36'
    },
    logoImg: {
      width: '224px',
      height: '69px',
      objectFit: 'contain',
      marginBottom: '1.75em'
    }
  };

  var styles$2 = {
    bannerTop: {
      height: '3px',
      backgroundColor: '#edc200',
      marginLeft: '11px',
      marginRight: '11px'
    },
    bannerBox: {
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
    bannerBoxInner: {
      display: 'flex',
      flexDirection: 'row'
    },
    bannerIcon: {
      paddingRight: '5px'
    },
    bannerTextGroup: {
      alignContent: 'left',
      lineHeight: '1.2em',
      marginLeft: '5px'
    },
    bannerText: {
      fontSize: '14px'
    },
    usePassLink: {
      marginLeft: '0.2em',
      textDecoration: 'underline'
    }
  };

  class Banner extends React__namespace.Component {
    constructor(props) {
      super(props);
    }
    render() {
      return /*#__PURE__*/React__namespace.createElement("div", null, /*#__PURE__*/React__namespace.createElement("div", {
        style: styles$2.bannerTop
      }), /*#__PURE__*/React__namespace.createElement("div", {
        className: "banner-box",
        style: styles$2.bannerBox
      }, /*#__PURE__*/React__namespace.createElement("div", {
        style: styles$2.bannerBoxInner
      }, /*#__PURE__*/React__namespace.createElement("img", {
        src: "https://s3-us-west-2.amazonaws.com/lumen-components-prod/assets/icons/icon-warning.svg",
        alt: "warning icon",
        style: styles$2.bannerIcon
      }), /*#__PURE__*/React__namespace.createElement("div", {
        style: styles$2.bannerTextGroup
      }, /*#__PURE__*/React__namespace.createElement("p", {
        style: styles$2.bannerText
      }, this._renderBannerContent())))));
    }
    _renderBannerContent() {
      return 'quiz_count' === this.props.trialType ? this._renderQuizCountBannerContent() : this._renderTimedTrialBannerContent();
    }
    _renderQuizCountBannerContent() {
      if ('expired' === this.props.paymentStatus) {
        return /*#__PURE__*/React__namespace.createElement("p", {
          style: styles$2.bannerText
        }, "You\u2019ve run out of activation passes. Course content is still available. However, you need to pay to activate the assessments in this course.");
      } else if ('can_extend' === this.props.paymentStatus) {
        return /*#__PURE__*/React__namespace.createElement("p", {
          style: styles$2.bannerText
        }, "You have run out of activation passes. Use a final one-time pass to access this assessment.", /*#__PURE__*/React__namespace.createElement("a", {
          href: this.props.redirectTo,
          style: styles$2.usePassLink
        }, "Use Pass"));
      }
    }
    _renderTimedTrialBannerContent() {
      if ('expired' === this.props.paymentStatus) {
        return /*#__PURE__*/React__namespace.createElement("p", {
          style: styles$2.bannerText
        }, "Your Trial Has Expired. Course content is still available. However, you need to pay to activate the assessments in this course.");
      } else if ('can_extend' === this.props.paymentStatus) {
        return /*#__PURE__*/React__namespace.createElement("p", {
          style: styles$2.bannerText
        }, "Your Trial Has Expired. Activate a one-time pass to extend your trial by 24 hours.", /*#__PURE__*/React__namespace.createElement("a", {
          href: this.props.redirectTo,
          style: styles$2.usePassLink
        }, "Activate One-time Pass"));
      }
    }
  }

  var styles$1 = {
    bodyWrapper: {
      marginLeft: '16px'
    },
    heading: {
      fontSize: '26px',
      fontWeight: 'bold',
      fontStyle: 'normal',
      fontStretch: 'normal',
      lineHeight: '1.23',
      letterSpacing: 'normal',
      textAlign: 'left',
      color: '#212b36',
      marginBottom: '12px'
    },
    footerBorder: {
      marginBottom: '16px',
      height: '0',
      borderBottom: '1px solid #ebecf0',
      maxWidth: '639px'
    },
    footerText: {
      fontSize: '12px'
    },
    footerLinks: {
      color: '#000',
      textDecoration: 'underline'
    },
    lumenAttributionWrapper: {
      display: 'flex',
      alignItems: 'center',
      fontSize: '12px',
      marginTop: '4em',
      height: '41px'
    },
    lumenLogo: {
      height: '41px',
      marginLeft: '1em',
      width: '88.7px'
    },
    lumenLogoLink: {
      height: '41px'
    }
  };

  var styles = {
    optionsWrapper: {
      padding: '20px 0'
    },
    optionItem: {
      backgroundColor: '#f4f6f8',
      border: 'solid 1px #dfe3e8',
      borderRadius: '3px',
      display: 'block',
      padding: '14px',
      marginBottom: '8px',
      minHeight: '36px',
      width: '531px'
    },
    optionItemContentLeft: {
      display: 'inline-block',
      width: '80%',
      verticalAlign: 'middle'
    },
    optionItemContentRight: {
      display: 'inline-block',
      width: '20%',
      verticalAlign: 'middle'
    },
    optionItemIconWrapper: {
      display: 'inline-block',
      verticalAlign: 'middle'
    },
    optionItemIcon: {
      height: '24px',
      width: '24px'
    },
    optionItemInfoIconWrapper: {
      display: 'inline-block',
      verticalAlign: 'middle',
      marginLeft: '8px'
    },
    optionItemInfoIcon: {
      height: '16px',
      width: '16px',
      cursor: 'help'
    },
    optionItemContentLeftWrapper: {
      display: 'inline-block',
      verticalAlign: 'middle',
      marginLeft: '20px'
    },
    optionItemContentLabel: {
      fontSize: '16px',
      fontWeight: 600,
      verticalAlign: 'middle'
    },
    optionItemContentSubLabel: {
      fontSize: '14px'
    },
    optionItemContentButton: {
      backgroundColor: '#1e74d1',
      border: '1px solid #004c9f',
      borderRadius: '3px',
      color: '#fff',
      fontSize: '14px',
      height: '36px',
      width: '106px',
      padding: 0,
      cursor: 'pointer'
    },
    optionItemContentButtonDisabled: {
      backgroundColor: '#8eb9e7',
      border: '1px solid #7ba6d6',
      borderRadius: '3px',
      color: '#fff',
      fontSize: '14px',
      height: '36px',
      width: '106px',
      padding: 0,
      cursor: 'not-allowed'
    },
    activationCodeInputGroup: {
      display: 'block'
    },
    activationCodeInput: {
      width: '320px',
      height: '36px',
      fontSize: '14px',
      paddingLeft: '13px'
    },
    activationCodeError: {
      fontSize: '14px',
      color: '#bf0711',
      display: 'inline-block',
      verticalAlign: 'middle',
      marginLeft: '10px',
      maxWidth: '500px'
    },
    errorMessageWrapper: {
      display: 'block',
      marginTop: '4px'
    },
    errorIcon: {
      width: '20px',
      height: '20px',
      display: 'inline-block',
      verticalAlign: 'middle'
    },
    activationCodeButton: {
      backgroundColor: '#1e74d1',
      border: '1px solid #004c9f',
      borderRadius: '3px',
      color: '#fff',
      display: 'block',
      fontSize: '14px',
      height: '36px',
      width: '193px',
      padding: 0,
      cursor: 'pointer',
      marginTop: '12px'
    },
    popOver: {
      width: '237px',
      height: '102px',
      backgroundColor: '#fff',
      borderRadius: '2px',
      boxShadow: '0 2px 16px 0 rgba(33, 43, 54, 0.08), 0 0 0 1px rgba(6, 44, 82, 0.1)',
      padding: '16px',
      fontSize: '12px',
      zIndex: 1,
      position: 'absolute',
      margin: '15px 0 0 5px'
    }
  };

  class OptionItemDropdown extends React__namespace.Component {
    constructor(props) {
      super(props);
      this.state = {
        showError: this.props.errors != undefined && this.props.errors.length > 0
      };
      this._onCodeInputChange = this._onCodeInputChange.bind(this);
    }
    render() {
      return /*#__PURE__*/React__namespace.createElement("div", null, this._renderOptionItemDropdown());
    }
    _renderOptionItemDropdown() {
      switch (this.props.item) {
        case 1:
          return /*#__PURE__*/React__namespace.createElement("div", null, /*#__PURE__*/React__namespace.createElement("div", {
            style: styles.activationCodeInputGroup
          }, /*#__PURE__*/React__namespace.createElement("input", {
            name: "code",
            style: styles.activationCodeInput,
            type: "text",
            placeholder: "Activation Code",
            onChange: this._onCodeInputChange
          }), this._renderErrorMessage(), /*#__PURE__*/React__namespace.createElement("input", {
            type: "hidden",
            name: "assessmentUrl",
            value: this.props.assessmentUrl
          })), /*#__PURE__*/React__namespace.createElement("button", {
            style: styles.activationCodeButton
          }, "Continue to Assessment"));
        case 2:
          return;
        case 3:
          return;
      }
    }
    _renderErrorMessage() {
      if (this.state.showError) {
        return /*#__PURE__*/React__namespace.createElement("div", {
          style: styles.errorMessageWrapper
        }, /*#__PURE__*/React__namespace.createElement("img", {
          style: styles.errorIcon,
          src: "https://s3-us-west-2.amazonaws.com/lumen-components-prod/assets/icons/icon-polaris-warning.png",
          alt: "warning icon"
        }), /*#__PURE__*/React__namespace.createElement("p", {
          style: styles.activationCodeError
        }, this.props.errors));
      }
    }
    _onCodeInputChange(e) {
      this.props.onCodeInputChange(e.target.value);
    }
  }

  class OptionItem extends React__namespace.Component {
    constructor(props) {
      super(props);
      this.state = {
        showDropdown: this.props.activationCodeErrors != undefined && this.props.activationCodeErrors.length > 0,
        showItemButton: true,
        hoveringInfo: false
      };
      this._handleClick = this._handleClick.bind(this);
      this._handleMouseOver = this._handleMouseOver.bind(this);
      this._handleMouseLeave = this._handleMouseLeave.bind(this);
    }
    render() {
      return /*#__PURE__*/React__namespace.createElement("div", {
        style: styles.optionItem
      }, /*#__PURE__*/React__namespace.createElement("div", {
        style: styles.optionItemContentLeft
      }, /*#__PURE__*/React__namespace.createElement("div", {
        style: styles.optionItemIconWrapper
      }, /*#__PURE__*/React__namespace.createElement("img", {
        style: styles.optionItemIcon,
        src: this.props.icon,
        alt: this.props.iconAlt
      })), /*#__PURE__*/React__namespace.createElement("div", {
        style: styles.optionItemContentLeftWrapper
      }, /*#__PURE__*/React__namespace.createElement("p", {
        style: styles.optionItemContentLabel
      }, this.props.label), /*#__PURE__*/React__namespace.createElement("p", {
        style: styles.optionItemContentSubLabel
      }, this.props.subLabel)), this._renderHoverHelp()), /*#__PURE__*/React__namespace.createElement("div", {
        style: styles.optionItemContentRight
      }, this._renderItemButton()), this._renderDropdown());
    }
    _renderHoverHelp() {
      console.log(this.props.infoIcon.length > 0);
      if (this.props.infoIcon.length > 0) {
        return /*#__PURE__*/React__namespace.createElement("div", {
          style: styles.optionItemInfoIconWrapper
        }, /*#__PURE__*/React__namespace.createElement("img", {
          style: styles.optionItemInfoIcon,
          src: this.props.infoIcon,
          alt: this.props.infoIconAlt,
          onMouseEnter: this._handleMouseOver,
          onMouseLeave: this._handleMouseLeave
        }), this._renderPopOver());
      }
    }
    _renderPopOver() {
      if (1 === this.props.item && this.state.hoveringInfo) {
        return /*#__PURE__*/React__namespace.createElement("span", {
          style: styles.popOver
        }, "Activation codes are often available from a campus bookstore. If you have questions on obtaining an activation code please ask your instructor.");
      }
    }
    _renderDropdown() {
      return this.state.showDropdown ? /*#__PURE__*/React__namespace.createElement(OptionItemDropdown, {
        item: this.props.item,
        assessmentUrl: this.props.assessmentUrl,
        errors: this.props.activationCodeErrors,
        onCodeInputChange: this.props.onCodeInputChange
      }) : null;
    }
    _renderItemButton() {
      if (1 === this.props.item && this.state.showDropdown) {
        return;
      } else if (2 === this.props.item) {
        return /*#__PURE__*/React__namespace.createElement(DirectPayButton, {
          style: this._getItemButtonStyles(),
          paymentStatus: this.props.paymentStatus,
          stripeKey: this.props.stripeKey,
          chargeAmount: this.state.total,
          institutionName: this.props.institutionName,
          chargeDescription: this.props.chargeDescription,
          stripeModalLogoUrl: this.props.stripeModalLogoUrl,
          endpointUrl: this.props.endpointUrl,
          userEmail: this.props.userEmail,
          zipcode: this.state.zipcode
        });
      } else if (this.state.showItemButton) {
        return /*#__PURE__*/React__namespace.createElement("button", {
          style: this._getItemButtonStyles(),
          onClick: this._handleClick,
          disabled: this._setButtonDisabled()
        }, this.props.buttonText);
      }
    }
    _setButtonDisabled() {
      if (this._noQuizPassesLeft() || this._trialExpired()) {
        return 'disabled';
      } else {
        return false;
      }
    }
    _noQuizPassesLeft() {
      return 'quiz_count' === this.props.trialType && 0 === this.props.trialPassesRemaining;
    }
    _trialExpired() {
      return ('can_extend' === this.props.paymentStatus || 'expired' === this.props.paymentStatus) && 1 > this.props.trialTimeRemaining;
    }
    _getItemButtonStyles() {
      if (this._noQuizPassesLeft() || this._trialExpired()) {
        return styles.optionItemContentButtonDisabled;
      } else {
        return styles.optionItemContentButton;
      }
    }
    _handleClick(e) {
      if (1 === this.props.item) {
        this.setState({
          showDropdown: !this.state.showDropdown,
          showItemButton: false
        });
      } else if (2 === this.props.item) {
        this.props.showCheckout();
      } else if (3 === this.props.item) {
        window.location = this.props.redirectTo;
      }
    }
    _handleMouseOver(e) {
      this.setState({
        hoveringInfo: true
      });
    }
    _handleMouseLeave(e) {
      this.setState({
        hoveringInfo: false
      });
    }
  }

  class MultiPayAccessOptions extends React__namespace.Component {
    constructor(props) {
      super(props);
      this.state = {
        activationCode: ''
      };
      this._onCodeInputChange = this._onCodeInputChange.bind(this);
      this._onSubmitCode = this._onSubmitCode.bind(this);
    }
    render() {
      return /*#__PURE__*/React__namespace.createElement("div", {
        style: styles.optionsWrapper
      }, /*#__PURE__*/React__namespace.createElement("form", {
        method: "POST",
        action: this.props.endpointUrl,
        onSubmit: this._onSubmitCode,
        className: "nolimit"
      }, /*#__PURE__*/React__namespace.createElement(OptionItem, {
        item: 1,
        icon: 'https://s3-us-west-2.amazonaws.com/lumen-components-prod/assets/icons/icon-store.png',
        iconAlt: 'an icon of a store',
        infoIcon: 'https://s3-us-west-2.amazonaws.com/lumen-components-prod/assets/icons/icon-material-info.png',
        infoIconAlt: 'info icon',
        label: 'Enter Activation Code',
        subLabel: '',
        buttonText: 'Enter Code',
        assessmentUrl: this.props.assessmentUrl,
        activationCodeErrors: this.props.activationCodeErrors,
        onCodeInputChange: this._onCodeInputChange
      })), /*#__PURE__*/React__namespace.createElement(OptionItem, {
        item: 2,
        icon: 'https://s3-us-west-2.amazonaws.com/lumen-components-prod/assets/icons/icon-credit-card.png',
        iconAlt: 'an icon of a credit card',
        infoIcon: '',
        infoIconAlt: '',
        label: `Pay $${this._calculateAmount()} Activation Fee Online`,
        subLabel: '',
        buttonText: 'Pay Now',
        showCheckout: this.props.showCheckout,
        activationCodeErrors: this.props.activationCodeErrors,
        amount_in_cents: this.props.chargeAmount,
        stripeKey: this.props.stripeKey,
        paymentStatus: this.props.paymentStatus,
        institutionName: this.props.institutionName,
        chargeDescription: this.props.chargeDescription,
        stripeModalLogoUrl: this.props.stripeModalLogoUrl,
        endpointUrl: this.props.endpointUrl,
        userEmail: this.props.userEmail,
        schoolLogoUrl: this.props.schoolLogoUrl
      }),
      // See: OHM-1300
      //   In goldi, props.allowTrial will not exist. In this case, show the trial option.
      //   OHM will always provide props.allowTrial.
      undefined === this.props.allowTrial || this.props.allowTrial ? this._showTrialOption() : '');
    }
    _showTrialOption() {
      return /*#__PURE__*/React__namespace.createElement(OptionItem, {
        item: 3,
        icon: 'https://s3-us-west-2.amazonaws.com/lumen-components-prod/assets/icons/icon-clock.png',
        iconAlt: 'an icon of a clock',
        infoIcon: '',
        infoIconAlt: '',
        label: this._getLabelText(),
        subLabel: 'quiz_count' === this.props.trialType ? `${this.props.trialPassesRemaining} of 2 Passes Available` : '',
        buttonText: this._getButtonText(),
        trialPassesRemaining: this.props.trialPassesRemaining,
        trialTimeRemaining: this.props.trialTimeRemaining,
        trialType: this.props.trialType,
        paymentStatus: this.props.paymentStatus,
        redirectTo: this.props.redirectTo,
        activationCodeErrors: this.props.activationCodeErrors
      });
    }
    _calculateAmount() {
      let amount = (this.props.chargeAmount / 100).toFixed(2);
      return 0 === amount % 1 ? amount | 0 : amount;
    }
    _getLabelText() {
      if ('quiz_count' === this.props.trialType) {
        return 'Use a Free Pass to Access Assessment';
      }
      if ('trial_not_started' === this.props.paymentStatus) {
        return 'Need More Time? Start a Two-week Trial';
      }
      if ('in_trial' === this.props.paymentStatus) {
        return `Continue Trial, ${this._getTimeRemaining()} Remaining`;
      }
      if ('can_extend' === this.props.paymentStatus || 'expired' === this.props.paymentStatus) {
        return 'Trial Expired';
      }
    }
    _getTimeRemaining() {
      let timeLeft = this.props.trialTimeRemaining;
      if (60 > timeLeft) {
        timeLeft = 'less than 1 Minute';
      } else if (60 < timeLeft && 120 > timeLeft) {
        timeLeft = '1 minute';
      } else if (3600 >= timeLeft) {
        timeLeft = `${Math.floor(timeLeft / 60)} Minutes`;
      } else if (3600 <= timeLeft && 7200 > timeLeft) {
        timeLeft = `${Math.floor(timeLeft / 3600)} Hour`;
      } else if (86400 > timeLeft) {
        timeLeft = `${Math.floor(timeLeft / 3600)} Hours`;
      } else if (86400 < timeLeft && 172800 > timeLeft) {
        timeLeft = '1 day';
      } else {
        timeLeft = `${(timeLeft / 86400).toFixed()} Days`;
      }
      return timeLeft;
    }
    _getButtonText() {
      if ('quiz_count' === this.props.trialType) {
        return 'Use Pass';
      } else {
        if ('trial_not_started' === this.props.paymentStatus) {
          return 'Start Trial';
        }
        if ('in_trial' === this.props.paymentStatus || 'can_extend' === this.props.paymentStatus || 'expired' === this.props.paymentStatus) {
          return 'Continue Trial';
        }
      }
    }
    _onCodeInputChange(code) {
      this.setState({
        code: code
      });
    }
    _onSubmitCode(e) {
      if (!this.state.code) {
        e.preventDefault();
      }
    }
  }

  class MultiPayCourseAssessmentActivation extends React__namespace.Component {
    constructor(props) {
      super(props);
    }
    render() {
      return /*#__PURE__*/React__namespace.createElement("div", {
        style: styles$1.bodyWrapper
      }, /*#__PURE__*/React__namespace.createElement("h1", {
        className: "activation-heading",
        style: styles$1.heading
      }, "Course Assessment Activation"), /*#__PURE__*/React__namespace.createElement("p", null, "To access this and future assessments for this course, select an option below."), /*#__PURE__*/React__namespace.createElement("p", null, "Course content is always available and free."), /*#__PURE__*/React__namespace.createElement(MultiPayAccessOptions, {
        allowTrial: this.props.allowTrial,
        trialType: this.props.trialType,
        trialPassesRemaining: this.props.trialPassesRemaining,
        trialTimeRemaining: this.props.trialTimeRemaining,
        paymentStatus: this.props.paymentStatus,
        chargeAmount: this.props.chargeAmount,
        showCheckout: this.props.showCheckout,
        redirectTo: this.props.redirectTo,
        activationCodeErrors: this.props.activationCodeErrors,
        endpointUrl: this.props.endpointUrl,
        assessmentUrl: this.props.assessmentUrl,
        amount_in_cents: this.props.chargeAmount,
        stripeKey: this.props.stripeKey,
        paymentStatus: this.props.paymentStatus,
        institutionName: this.props.institutionName,
        chargeDescription: this.props.chargeDescription,
        stripeModalLogoUrl: this.props.stripeModalLogoUrl,
        endpointUrl: this.props.endpointUrl,
        userEmail: this.props.userEmail,
        schoolLogoUrl: this.props.schoolLogoUrl
      }), /*#__PURE__*/React__namespace.createElement("div", {
        style: styles$1.footerBorder
      }), /*#__PURE__*/React__namespace.createElement("p", {
        style: styles$1.footerText
      }, "By clicking on Enter Code, Pay Now, or ", this._renderTrialContinueText(), " you agree to Lumen Learning's ", /*#__PURE__*/React__namespace.createElement("a", {
        target: "_blank",
        href: "https://lumenlearning.com/policies/terms-of-service",
        style: styles$1.footerLinks
      }, "Terms of Service"), " and ", /*#__PURE__*/React__namespace.createElement("a", {
        target: "_blank",
        href: "https://lumenlearning.com/policies/privacy-policy",
        style: styles$1.footerLinks
      }, "Privacy Policy"), "."), this._renderFooterLogo());
    }
    _renderTrialContinueText() {
      if (this.props.trialType === "quiz_count") {
        return "Use Pass";
      } else {
        return this.props.paymentStatus === "in_trial" ? "Continue Trial" : "Start Trial";
      }
    }
    _renderFooterLogo() {
      if (this.props.schoolLogoUrl != null && this.props.schoolLogoUrl !== '') {
        return /*#__PURE__*/React__namespace.createElement("div", {
          className: "lumen-attribution",
          style: styles$1.lumenAttributionWrapper
        }, /*#__PURE__*/React__namespace.createElement("span", null, "Open Courseware by "), /*#__PURE__*/React__namespace.createElement("a", {
          style: styles$1.lumenLogoLink,
          href: "https://www.lumenlearning.com",
          target: "_blank"
        }, /*#__PURE__*/React__namespace.createElement("img", {
          src: "https://s3-us-west-2.amazonaws.com/lumen-components/assets/Lumen-300x138.png",
          alt: "Lumen Learning logo",
          className: "lumen-logo",
          style: styles$1.lumenLogo
        })));
      }
    }
  }

  class MultiPayPage extends React__namespace.Component {
    constructor(props) {
      super(props);
      this.state = {
        showCheckout: false
      };
      this._showCheckout = this._showCheckout.bind(this);
    }
    render() {
      return /*#__PURE__*/React__namespace.createElement("div", {
        style: styles$3.pageWrapper
      }, this._renderBanner(), /*#__PURE__*/React__namespace.createElement("div", {
        className: "multipay-page-wrapper",
        style: styles$3.pageWrapperInner
      }, /*#__PURE__*/React__namespace.createElement("img", {
        src: this._getLogoUrl(),
        alt: this._getLogoAltText(),
        style: styles$3.logoImg
      }), /*#__PURE__*/React__namespace.createElement("div", null, this._getPageBody())));
    }
    _renderBanner() {
      // See: OHM-1300
      //   In goldi, props.allowTrial will not exist. In this case, show the trial option.
      //   OHM will always provide props.allowTrial.
      if (undefined !== this.props.allowTrial && !this.props.allowTrial) {
        return;
      }
      if ('in_trial' === this.props.paymentStatus || 'trial_not_started' === this.props.paymentStatus || undefined != this.props.activationCodeErrors || this.state.showCheckout) {
        return;
      }
      if (this._noQuizPassesLeft() || this._trialExpired) {
        return /*#__PURE__*/React__namespace.createElement(Banner, {
          trialType: this.props.trialType,
          paymentStatus: this.props.paymentStatus,
          redirectTo: this.props.redirectTo
        });
      }
    }
    _noQuizPassesLeft() {
      return 'quiz_count' === this.props.trialType && 0 === this.props.trialPassesRemaining;
    }
    _trialExpired() {
      return ('can_extend' === this.props.paymentStatus || 'expired' === this.props.paymentStatus) && 1 > this.props.trialTimeRemaining;
    }
    _showCheckout() {
      let showCheckout = !this.state.showCheckout;
      this.setState({
        showCheckout: showCheckout
      });
    }
    _getLogoUrl() {
      //return this.props.schoolLogoUrl || 'https://s3-us-west-2.amazonaws.com/lumen-platform-assets/images/lumen-open-courseware.png';
      return this.props.schoolLogoUrl || 'https://content-cdn.one.lumenlearning.com/wp-content/uploads/2023/09/20212456/lumen-primary-logo.png';
    }
    _getLogoAltText() {
      return this.props.institutionName ? `${this.props.institutionName} logo` : 'Lumen Open Courseware logo';
    }
    _getPageBody() {
      if (this.state.showCheckout) {
        return /*#__PURE__*/React__namespace.createElement(CheckoutTaxPage, {
          amount_in_cents: this.props.chargeAmount,
          stripeKey: this.props.stripeKey,
          paymentStatus: this.props.paymentStatus,
          institutionName: this.props.institutionName,
          chargeDescription: this.props.chargeDescription,
          stripeModalLogoUrl: this.props.stripeModalLogoUrl,
          endpointUrl: this.props.endpointUrl,
          userEmail: this.props.userEmail,
          schoolLogoUrl: this.props.schoolLogoUrl
        });
      } else {
        return /*#__PURE__*/React__namespace.createElement(MultiPayCourseAssessmentActivation, {
          allowTrial: this.props.allowTrial,
          trialType: this.props.trialType,
          trialPassesRemaining: this.props.trialPassesRemaining,
          trialTimeRemaining: this.props.trialTimeRemaining,
          chargeAmount: this.props.chargeAmount,
          showCheckout: this._showCheckout,
          redirectTo: this.props.redirectTo,
          activationCodeErrors: this.props.activationCodeErrors || [],
          assessmentUrl: this.props.assessmentUrl,
          amount_in_cents: this.props.chargeAmount,
          stripeKey: this.props.stripeKey,
          paymentStatus: this.props.paymentStatus,
          institutionName: this.props.institutionName,
          chargeDescription: this.props.chargeDescription,
          stripeModalLogoUrl: this.props.stripeModalLogoUrl,
          endpointUrl: this.props.endpointUrl,
          userEmail: this.props.userEmail,
          schoolLogoUrl: this.props.schoolLogoUrl
        });
      }
    }
  }

  var direct_pay_components = {
    renderDirectPayLandingPage: function (elementId, props) {
      React__namespace.render( /*#__PURE__*/React__namespace.createElement(DirectPayLandingPage, props), document.getElementById(elementId));
    },
    renderDirectPayCourseActivation: function (elementId, props) {
      React__namespace.render( /*#__PURE__*/React__namespace.createElement(DirectPayCourseActivation, props), document.getElementById(elementId));
    },
    renderDirectPayButton: function (elementId, props) {
      React__namespace.render( /*#__PURE__*/React__namespace.createElement(DirectPayButton, props), document.getElementById(elementId));
    },
    renderDirectPayConfirmation: function (elementId, props) {
      React__namespace.render( /*#__PURE__*/React__namespace.createElement(DirectPayConfirmation, props), document.getElementById(elementId));
    },
    renderDirectPayHeaderAlert: function (elementId, props) {
      React__namespace.render( /*#__PURE__*/React__namespace.createElement(DirectPayAlertHeader, props), document.getElementById(elementId));
    },
    renderCheckoutTaxPage: function (elementId, props) {
      React__namespace.render( /*#__PURE__*/React__namespace.createElement(CheckoutTaxPage, props), document.getElementById(elementId));
    },
    // Multi Pay
    renderMultiPayPage: function (elementId, props) {
      React__namespace.render( /*#__PURE__*/React__namespace.createElement(MultiPayPage, props), document.getElementById(elementId));
    }
  };

  return direct_pay_components;

})(React);
