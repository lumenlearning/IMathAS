var directPayComponents = (function (React) {
'use strict';

var styles = {
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

var styles$1 = {
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
  zipcode: {
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
  zipcodeError: {
    fontSize: '14px',
    color: '#bf0711',
    display: 'inline-block',
    verticalAlign: 'middle',
    marginLeft: '4px'
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
  }, {
    key: 'render',
    value: function render() {
      return React.createElement(
        'div',
        { id: 'payment-button-container', className: 'form-control', style: { paddingBottom: '13px', paddingTop: '13px' } },
        React.createElement(
          'form',
          { id: 'payment-button-form', action: this.props.endpointUrl, onClick: this._openCheckout, method: 'POST' },
          React.createElement('input', { type: 'hidden', id: 'zipcode', name: 'zipcode', value: this.props.zipcode })
        )
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

var CheckoutTaxPage = function (_React$Component) {
  inherits(CheckoutTaxPage, _React$Component);

  function CheckoutTaxPage(props) {
    classCallCheck(this, CheckoutTaxPage);

    var _this = possibleConstructorReturn(this, (CheckoutTaxPage.__proto__ || Object.getPrototypeOf(CheckoutTaxPage)).call(this, props));

    _this.state = {
      zipcode: '',
      taxAmount: '-',
      total: '-',
      errors: []
    };

    _this._setZipCode = _this._setZipCode.bind(_this);
    _this._handleKeyUp = _this._handleKeyUp.bind(_this);
    return _this;
  }

  createClass(CheckoutTaxPage, [{
    key: 'render',
    value: function render() {
      return React.createElement(
        'div',
        { style: styles$1.taxPageWrapper },
        React.createElement(
          'h1',
          { style: styles$1.heading },
          'Checkout'
        ),
        React.createElement(
          'h2',
          { style: styles$1.subHeading },
          'Calculate Taxes'
        ),
        React.createElement(
          'label',
          { htmlFor: 'zipcode', style: styles$1.zipcodeLabel },
          'Enter your 5-digit zip code'
        ),
        React.createElement('input', {
          type: 'text',
          id: 'zipcode',
          name: 'zipcode',
          style: styles$1.zipcode,
          onChange: this._setZipCode,
          maxLength: '5'
        }),
        React.createElement(
          'div',
          { style: styles$1.table },
          React.createElement(
            'div',
            { style: styles$1.tableHead },
            'Cost Summary'
          ),
          React.createElement(
            'div',
            { style: styles$1.columnOne },
            React.createElement(
              'div',
              { style: styles$1.assessmentActivation },
              'Assessment Activation'
            ),
            React.createElement(
              'div',
              { style: styles$1.preTaxValue },
              this._convertToDollars(this.props.amount_in_cents)
            )
          ),
          React.createElement(
            'div',
            { style: styles$1.columnTwo },
            React.createElement(
              'div',
              { style: styles$1.taxes },
              'Taxes'
            ),
            React.createElement(
              'div',
              { style: styles$1.taxValueToCollect },
              this._convertToDollars(this.state.taxAmount)
            )
          ),
          React.createElement(
            'div',
            { style: styles$1.columnThree },
            React.createElement(
              'div',
              { style: styles$1.total },
              'Total'
            ),
            React.createElement(
              'div',
              { style: styles$1.totalValueToCollect },
              this._convertToDollars(this.state.total)
            )
          ),
          this._renderErrors()
        ),
        this._renderPayButton(),
        this._renderFooterLogo()
      );
    }
  }, {
    key: '_renderErrors',
    value: function _renderErrors() {
      if (this.state.errors.length > 0) {
        return React.createElement(
          'div',
          { style: styles$1.errorMessageWrapper },
          React.createElement('img', { style: styles$1.errorIcon, src: 'https://s3-us-west-2.amazonaws.com/lumen-components-prod/assets/icons/icon-polaris-warning.png', alt: 'warning icon' }),
          React.createElement(
            'p',
            { style: styles$1.zipcodeError },
            this.state.errors.map(function (error) {
              return React.createElement(
                'p',
                { style: styles$1.zipcodeError },
                error
              );
            })
          )
        );
      }
    }
  }, {
    key: '_renderFooterLogo',
    value: function _renderFooterLogo() {
      if (this.props.schoolLogoUrl != null && this.props.schoolLogoUrl !== '') {
        return React.createElement(
          'div',
          { className: 'lumen-attribution', style: styles$1.lumenAttributionWrapper },
          React.createElement(
            'span',
            null,
            'Open Courseware by '
          ),
          React.createElement(
            'a',
            { style: styles$1.lumenLogoLink, href: "https://www.lumenlearning.com", target: '_blank' },
            React.createElement('img', { src: 'https://s3-us-west-2.amazonaws.com/lumen-components/assets/Lumen-300x138.png',
              alt: 'Lumen Learning logo', className: 'lumen-logo',
              style: styles$1.lumenLogo })
          )
        );
      }
    }
  }, {
    key: '_renderPayButton',
    value: function _renderPayButton() {
      if ('-' != this.state.total) {
        return React.createElement(DirectPayButton, {
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
        return React.createElement(
          'button',
          { style: styles$1.payNowDisabled, disabled: 'true' },
          'Pay Now'
        );
      }
    }
  }, {
    key: '_setZipCode',
    value: function _setZipCode(e) {
      if (e.target.value.length === 5) {
        this.setState({
          taxAmount: '-',
          total: '-'
        });
        this._getTaxAmount();
        this.setState({
          zipcode: e.target.value
        });
      } else {
        this.setState({
          taxAmount: '-',
          total: '-'
        });
      }
    }
  }, {
    key: '_convertToDollars',
    value: function _convertToDollars(amount_in_cents) {
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
  }, {
    key: '_getTaxAmount',
    value: function _getTaxAmount() {
      var _this2 = this;

      var data = {
        amount_in_cents: parseInt(this.props.amount_in_cents, 10),
        zipcode: this.state.zipcode
      };

      fetch(this._getTaxApiUrl(), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
      }).then(function (res) {
        var promise = res.json();

        promise.then(function (value) {
          if (value.errors != undefined) {
            _this2.setState({
              taxAmount: '-',
              total: '-',
              errors: value.errors
            });
          }
          _this2.setState({
            taxAmount: value.tax_amount_in_cents,
            total: value.tax_amount_in_cents + parseInt(_this2.props.amount_in_cents, 10),
            errors: []
          });
        });
      });
    }
  }, {
    key: '_getTaxApiUrl',
    value: function _getTaxApiUrl() {
      if (window.location.host.indexOf('ludev.team') !== -1) {
        return 'https://admin.ludev.team/api/student_pay/tax';
      } else {
        return 'https://admin.lumenlearning.com/api/student_pay/tax';
      }
    }
  }]);
  return CheckoutTaxPage;
}(React.Component);

var DirectPayCourseActivation = function (_React$Component) {
  inherits(DirectPayCourseActivation, _React$Component);

  function DirectPayCourseActivation(props) {
    classCallCheck(this, DirectPayCourseActivation);

    var _this = possibleConstructorReturn(this, (DirectPayCourseActivation.__proto__ || Object.getPrototypeOf(DirectPayCourseActivation)).call(this, props));

    _this.state = {
      windowWidth: null,
      showCheckout: false
    };

    _this._handleWindowResize = _this._handleWindowResize.bind(_this);
    _this._toggleCheckout = _this._toggleCheckout.bind(_this);
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
      if (this.state.showCheckout) {
        return React.createElement(CheckoutTaxPage, {
          amount_in_cents: this.props.chargeAmount,
          stripeKey: this.props.stripeKey,
          paymentStatus: this.props.paymentStatus,
          institutionName: this.props.institutionName,
          chargeDescription: this.props.chargeDescription,
          stripeModalLogoUrl: this.props.stripeModalLogoUrl,
          endpointUrl: this.props.endpointUrl,
          userEmail: this.props.userEmail
        });
      } else {
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
                this._headerLanguage()
              ),
              React.createElement(
                'h3',
                { className: 'course-title',
                  style: styles.courseTitle },
                this.props.courseTitle + ': ' + priceInDollars
              ),
              window.innerWidth < 800 ? React.createElement(
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
              React.createElement(
                'button',
                { style: styles.payNowButton, onClick: this._toggleCheckout },
                'Pay Now'
              )
            ),
            this._renderRightColumn()
          )
        );
      }
    }
  }, {
    key: 'componentWillUnmount',
    value: function componentWillUnmount() {
      window.removeEventListener("resize");
    }
  }, {
    key: '_toggleCheckout',
    value: function _toggleCheckout() {
      var showCheckout = this.state.showCheckout;

      this.setState({
        showCheckout: !showCheckout
      });
    }
  }, {
    key: '_renderRightColumn',
    value: function _renderRightColumn() {
      if (window.innerWidth < 800) {
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
  }, {
    key: '_trialStartLanguage',
    value: function _trialStartLanguage() {
      if (this.props.trialType === "quiz_count") {
        return "You can access up to two assessments before activation is required. 2 of 2 passes available.";
      }
      return "You can access your assessments for two weeks before activation is required.";
    }
  }, {
    key: '_trialContinueLanguage',
    value: function _trialContinueLanguage() {
      if (this.props.trialType === "quiz_count") {
        var trialPassesRemaining = this.props.trialPassesRemaining;
        return "You can access up to two assessments before activation is required. " + trialPassesRemaining + " of 2 passes available.";
      } else {
        var trialTimeRemaining = this._getTrialTimeRemainingWords();
        return "You can continue to access your assessments for " + trialTimeRemaining + " before activation is required.";
      }
    }
  }, {
    key: '_headerLanguage',
    value: function _headerLanguage() {
      var language = "Course Assessment Activation";

      if (this.props.schoolLogoUrl != null && this.props.schoolLogoUrl !== '') {
        language = "Assessment Activation";
      }

      return language;
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

      if (this.state.windowWidth < 800) {
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

      if (this.state.windowWidth < 800 && this.state.windowWidth > 414) {
        styles$$1 = {
          width: '340px'
        };
      }

      return styles$$1;
    }
  }]);
  return DirectPayCourseActivation;
}(React.Component);

var styles$2 = {
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
      var date = new Date();
      var timestamp = date.toLocaleString();

      if (this.props.confirmationNum != undefined) {
        return React.createElement(
          'div',
          { style: styles$2.confirmationPageWrapper },
          React.createElement(
            'div',
            { className: 'confirmation-wrapper', style: styles$2.confirmationWrapper },
            React.createElement(
              'h1',
              { className: 'heading', style: styles$2.confirmationHeading },
              'Thank You!'
            ),
            React.createElement(
              'h2',
              { className: 'subheading', style: styles$2.confirmationSubheading },
              'You can now access all online assessments for ' + this.props.courseTitle + '.'
            ),
            React.createElement(
              'div',
              { className: 'confirmation-text-wrapper', style: styles$2.confirmationTextWrapper },
              React.createElement(
                'p',
                { style: styles$2.confirmationText },
                'Confirmation #' + this.props.confirmationNum
              ),
              React.createElement(
                'p',
                {
                  style: styles$2.confirmationText },
                'A receipt has been sent to your email address at ' + this.props.userEmail + '.'
              ),
              React.createElement('br', null),
              React.createElement(
                'p',
                { style: styles$2.confirmationText },
                'The purchase will show up as Lumen Learning on your debit or credit card statement.'
              )
            ),
            React.createElement(
              'button',
              { style: styles$2.continueButton, onClick: this._handleClick },
              'Continue'
            )
          )
        );
      } else {
        return React.createElement(
          'div',
          { style: styles$2.confirmationPageWrapper },
          React.createElement(
            'div',
            { className: 'confirmation-wrapper', style: styles$2.confirmationWrapper },
            React.createElement(
              'h1',
              { className: 'heading', style: styles$2.confirmationHeading },
              'You\'re all set!'
            ),
            React.createElement(
              'h2',
              { className: 'subheading', style: styles$2.confirmationSubheading },
              'Thank you for submitting your Lumen OHM course activation code. Please print this screen or save it as a PDF for your records.'
            ),
            React.createElement(
              'div',
              { className: 'confirmation-text-wrapper', style: styles$2.confirmationTextWrapper },
              React.createElement(
                'p',
                { style: styles$2.confirmationDetailValue },
                React.createElement(
                  'span',
                  {
                    style: styles$2.confirmationDetailKey },
                  'Student Name: '
                ),
                ' ',
                this.props.studentName,
                ' '
              ),
              React.createElement(
                'p',
                { style: styles$2.confirmationDetailValue },
                React.createElement(
                  'span',
                  {
                    style: styles$2.confirmationDetailKey },
                  'Course Name: '
                ),
                ' ',
                this.props.courseTitle,
                ' '
              ),
              React.createElement(
                'p',
                { style: styles$2.confirmationDetailValue },
                React.createElement(
                  'span',
                  {
                    style: styles$2.confirmationDetailKey },
                  'Activation Code Used: '
                ),
                ' ',
                this.props.activationCode.toUpperCase()
              ),
              React.createElement(
                'p',
                { style: styles$2.confirmationDetailValue },
                React.createElement(
                  'span',
                  {
                    style: styles$2.confirmationDetailKey },
                  'Timestamp: '
                ),
                ' ',
                timestamp,
                ' '
              )
            ),
            React.createElement(
              'button',
              { style: styles$2.continueButton, onClick: this._handleClick },
              'Continue'
            ),
            this._renderFooterLogo()
          )
        );
      }
    }
  }, {
    key: '_handleClick',
    value: function _handleClick() {
      window.location = this.props.redirectTo;
    }
  }, {
    key: '_renderFooterLogo',
    value: function _renderFooterLogo() {
      if (this.props.schoolLogoUrl != null && this.props.schoolLogoUrl !== '') {
        return React.createElement(
          'div',
          { className: 'lumen-attribution', style: styles$2.lumenAttributionWrapper },
          React.createElement(
            'span',
            null,
            'Open Courseware by '
          ),
          React.createElement(
            'a',
            { style: styles$2.lumenLogoLink, href: "https://www.lumenlearning.com", target: '_blank' },
            React.createElement('img', { src: 'https://s3-us-west-2.amazonaws.com/lumen-components/assets/Lumen-300x138.png',
              alt: 'Lumen Learning logo', className: 'lumen-logo',
              style: styles$2.lumenLogo })
          )
        );
      }
    }
  }]);
  return DirectPayConfirmation;
}(React.Component);

var styles$3 = {
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
          { className: "landing-page-wrapper", style: styles$3.landingPageWrapper },
          React.createElement("img", {
            src: this._getSchoolLogoUrl(),
            alt: this.props.institutionName + " logo",
            style: { width: '224px', height: '69px', objectFit: 'contain' }
          }),
          React.createElement(
            "div",
            { style: { paddingTop: '40px' } },
            this._loadCorrectView()
          ),
          this._renderFooterLogo()
        )
      );
    }
  }, {
    key: "_getSchoolLogoUrl",
    value: function _getSchoolLogoUrl() {
      return this.props.schoolLogoUrl || 'https://s3-us-west-2.amazonaws.com/lumen-platform-assets/images/lumen-open-courseware.png';
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
          redirectTo: this.props.redirectTo,
          studentName: this.props.studentName,
          activationCode: this.props.activationCode,
          timestamp: this.props.timestamp
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
          trialTimeRemaining: this.props.trialTimeRemaining,
          trialPassesRemaining: this.props.trialPassesRemaining,
          trialType: this.props.trialType
        });
      }
    }
  }, {
    key: "_renderFooterLogo",
    value: function _renderFooterLogo() {
      if (this.props.schoolLogoUrl != null && this.props.schoolLogoUrl !== '') {
        return React.createElement(
          "div",
          { className: "lumen-attribution", style: styles$3.lumenAttributionWrapper },
          React.createElement(
            "span",
            null,
            "Open Courseware by "
          ),
          React.createElement(
            "a",
            { href: "https://www.lumenlearning.com", target: "_blank" },
            React.createElement("img", { src: "https://s3-us-west-2.amazonaws.com/lumen-components/assets/Lumen-300x138.png",
              alt: "Lumen Learning logo", className: "lumen-logo",
              style: styles$3.lumenLogo })
          )
        );
      }
    }
  }, {
    key: "_renderCorrectHeaderText",
    value: function _renderCorrectHeaderText() {
      var language = void 0;

      if (this.props.paymentStatus === 'expired') {
        language = " Course content is still available. However, you need to pay to activate the assessments in this course. ";
      } else if (this.props.paymentStatus === 'can_extend') {
        language = this._extendLanguage();
      } else {
        return '';
      }

      return language;
    }
  }, {
    key: "_extendLanguage",
    value: function _extendLanguage() {
      if (this.props.trialType === "quiz_count") {
        return " Use a final one-time pass to access this assessment. ";
      } else {
        return " Activate a one-time pass to extend your trial by 24 hours. ";
      }
    }
  }, {
    key: "_renderLinkText",
    value: function _renderLinkText() {
      return this.props.trialType === "quiz_count" ? "Use Assessment Pass" : "Activate One-time Pass";
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
            { className: "header-box", style: styles$3.headerBox },
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
                { style: { alignContent: 'left', lineHeight: '1.2em', marginLeft: '5px' } },
                React.createElement(
                  "p",
                  { style: { fontSize: '14px' } },
                  this.props.trialType === "quiz_count" ? "You've run out of activation passes." : "Your Trial Has Expired.",
                  React.createElement(
                    "span",
                    { className: "header-text" },
                    this._renderCorrectHeaderText(),
                    this.props.paymentStatus === 'can_extend' ? React.createElement(
                      "a",
                      { href: this.props.redirectTo, style: { textDecoration: 'underline', color: '#1e74d1' } },
                      this._renderLinkText()
                    ) : ""
                  )
                )
              )
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

var styles$4 = {
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

var styles$5 = {
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

var Banner = function (_React$Component) {
    inherits(Banner, _React$Component);

    function Banner(props) {
        classCallCheck(this, Banner);
        return possibleConstructorReturn(this, (Banner.__proto__ || Object.getPrototypeOf(Banner)).call(this, props));
    }

    createClass(Banner, [{
        key: 'render',
        value: function render() {
            return React.createElement(
                'div',
                null,
                React.createElement('div', { style: styles$5.bannerTop }),
                React.createElement(
                    'div',
                    { className: 'banner-box', style: styles$5.bannerBox },
                    React.createElement(
                        'div',
                        { style: styles$5.bannerBoxInner },
                        React.createElement('img', {
                            src: 'https://s3-us-west-2.amazonaws.com/lumen-components-prod/assets/icons/icon-warning.svg',
                            alt: 'warning icon',
                            style: styles$5.bannerIcon
                        }),
                        React.createElement(
                            'div',
                            { style: styles$5.bannerTextGroup },
                            React.createElement(
                                'p',
                                { style: styles$5.bannerText },
                                this._renderBannerContent()
                            )
                        )
                    )
                )
            );
        }
    }, {
        key: '_renderBannerContent',
        value: function _renderBannerContent() {
            if ('quiz_count' === this.props.trialType) {
                if ('expired' === this.props.paymentStatus) {
                    return React.createElement(
                        'p',
                        { style: styles$5.bannerText },
                        'You\u2019ve run out of activation passes. Course content is still available. However, you need to pay to activate the assessments in this course.'
                    );
                } else if ('can_extend' === this.props.paymentStatus) {
                    return React.createElement(
                        'p',
                        { style: styles$5.bannerText },
                        'You have run out of activation passes. Use a final one-time pass to access this assessment.',
                        React.createElement(
                            'a',
                            { href: this.props.redirectTo, style: styles$5.usePassLink },
                            'Use Pass'
                        )
                    );
                }
            } else {
                return React.createElement(
                    'p',
                    { style: styles$5.bannerText },
                    'Your Trial Has Expired. Activate a one-time pass to extend your trial by 24 hours.',
                    React.createElement(
                        'a',
                        { href: this.props.redirectTo, style: styles$5.usePassLink },
                        'Activate One-time Pass'
                    )
                );
            }
        }
    }]);
    return Banner;
}(React.Component);

var styles$6 = {
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

var styles$7 = {
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

var OptionItemDropdown = function (_React$Component) {
    inherits(OptionItemDropdown, _React$Component);

    function OptionItemDropdown(props) {
        classCallCheck(this, OptionItemDropdown);

        var _this = possibleConstructorReturn(this, (OptionItemDropdown.__proto__ || Object.getPrototypeOf(OptionItemDropdown)).call(this, props));

        _this.state = {
            showError: _this.props.errors != undefined && _this.props.errors.length > 0
        };

        _this._onCodeInputChange = _this._onCodeInputChange.bind(_this);
        return _this;
    }

    createClass(OptionItemDropdown, [{
        key: 'render',
        value: function render() {
            return React.createElement(
                'div',
                null,
                this._renderOptionItemDropdown()
            );
        }
    }, {
        key: '_renderOptionItemDropdown',
        value: function _renderOptionItemDropdown() {
            switch (this.props.item) {
                case 1:
                    return React.createElement(
                        'div',
                        null,
                        React.createElement(
                            'div',
                            { style: styles$7.activationCodeInputGroup },
                            React.createElement('input', {
                                name: 'code',
                                style: styles$7.activationCodeInput,
                                type: 'text',
                                placeholder: 'Activation Code',
                                onChange: this._onCodeInputChange
                            }),
                            this._renderErrorMessage(),
                            React.createElement('input', {
                                type: 'hidden',
                                name: 'assessmentUrl',
                                value: this.props.assessmentUrl })
                        ),
                        React.createElement(
                            'button',
                            {
                                style: styles$7.activationCodeButton
                            },
                            'Continue to Assessment'
                        )
                    );
                case 2:
                    return;
                case 3:
                    return;
            }
        }
    }, {
        key: '_renderErrorMessage',
        value: function _renderErrorMessage() {
            if (this.state.showError) {
                return React.createElement(
                    'div',
                    { style: styles$7.errorMessageWrapper },
                    React.createElement('img', { style: styles$7.errorIcon, src: 'https://s3-us-west-2.amazonaws.com/lumen-components-prod/assets/icons/icon-polaris-warning.png', alt: 'warning icon' }),
                    React.createElement(
                        'p',
                        { style: styles$7.activationCodeError },
                        this.props.errors
                    )
                );
            }
        }
    }, {
        key: '_onCodeInputChange',
        value: function _onCodeInputChange(e) {
            this.props.onCodeInputChange(e.target.value);
        }
    }]);
    return OptionItemDropdown;
}(React.Component);

var OptionItem = function (_React$Component) {
    inherits(OptionItem, _React$Component);

    function OptionItem(props) {
        classCallCheck(this, OptionItem);

        var _this = possibleConstructorReturn(this, (OptionItem.__proto__ || Object.getPrototypeOf(OptionItem)).call(this, props));

        _this.state = {
            showDropdown: _this.props.activationCodeErrors != undefined && _this.props.activationCodeErrors.length > 0,
            showItemButton: true,
            hoveringInfo: false
        };

        _this._handleClick = _this._handleClick.bind(_this);
        _this._handleMouseOver = _this._handleMouseOver.bind(_this);
        _this._handleMouseLeave = _this._handleMouseLeave.bind(_this);
        return _this;
    }

    createClass(OptionItem, [{
        key: 'render',
        value: function render() {
            return React.createElement(
                'div',
                { style: styles$7.optionItem },
                React.createElement(
                    'div',
                    { style: styles$7.optionItemContentLeft },
                    React.createElement(
                        'div',
                        { style: styles$7.optionItemIconWrapper },
                        React.createElement('img', { style: styles$7.optionItemIcon, src: this.props.icon, alt: this.props.iconAlt })
                    ),
                    React.createElement(
                        'div',
                        { style: styles$7.optionItemContentLeftWrapper },
                        React.createElement(
                            'p',
                            { style: styles$7.optionItemContentLabel },
                            this.props.label
                        ),
                        React.createElement(
                            'p',
                            { style: styles$7.optionItemContentSubLabel },
                            this.props.subLabel
                        )
                    ),
                    React.createElement(
                        'div',
                        { style: styles$7.optionItemInfoIconWrapper },
                        React.createElement('img', {
                            style: styles$7.optionItemInfoIcon,
                            src: this.props.infoIcon,
                            alt: this.props.infoIconAlt,
                            onMouseEnter: this._handleMouseOver,
                            onMouseLeave: this._handleMouseLeave
                        }),
                        this._renderPopOver()
                    )
                ),
                React.createElement(
                    'div',
                    { style: styles$7.optionItemContentRight },
                    this._renderItemButton()
                ),
                this._renderDropdown()
            );
        }
    }, {
        key: '_renderPopOver',
        value: function _renderPopOver() {
            if (1 === this.props.item && this.state.hoveringInfo) {
                return React.createElement(
                    'span',
                    { style: styles$7.popOver },
                    'Activation codes are often available from a campus bookstore. If you have questions on obtaining an activation code please ask your instructor.'
                );
            }
        }
    }, {
        key: '_renderDropdown',
        value: function _renderDropdown() {
            return this.state.showDropdown ? React.createElement(OptionItemDropdown, {
                item: this.props.item,
                assessmentUrl: this.props.assessmentUrl,
                errors: this.props.activationCodeErrors,
                onCodeInputChange: this.props.onCodeInputChange
            }) : null;
        }
    }, {
        key: '_renderItemButton',
        value: function _renderItemButton() {
            if (1 === this.props.item && this.state.showDropdown) {
                return;
            } else if (this.state.showItemButton) {
                return React.createElement(
                    'button',
                    {
                        style: this._getItemButtonStyles(),
                        onClick: this._handleClick,
                        disabled: this._setButtonDisabled()
                    },
                    this.props.buttonText
                );
            }
        }
    }, {
        key: '_setButtonDisabled',
        value: function _setButtonDisabled() {
            if (this._noQuizPassesLeft() || this._trialExpired() || this._noExtensionsLeft()) {
                return 'disabled';
            } else {
                return false;
            }
        }
    }, {
        key: '_noQuizPassesLeft',
        value: function _noQuizPassesLeft() {
            return 'quiz_count' === this.props.trialType && 0 === this.props.trialPassesRemaining;
        }
    }, {
        key: '_trialExpired',
        value: function _trialExpired() {
            return ('can_extend' === this.props.paymentStatus || 'expired' === this.props.paymentStatus) && 0 === this.props.trialTimeRemaining;
        }
    }, {
        key: '_noExtensionsLeft',
        value: function _noExtensionsLeft() {
            return 'expired' === this.props.paymentStatus && 3 === this.props.item;
        }
    }, {
        key: '_getItemButtonStyles',
        value: function _getItemButtonStyles() {
            if (this._noQuizPassesLeft() || this._trialExpired() || this._noExtensionsLeft()) {
                return styles$7.optionItemContentButtonDisabled;
            } else {
                return styles$7.optionItemContentButton;
            }
        }
    }, {
        key: '_handleClick',
        value: function _handleClick(e) {
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
    }, {
        key: '_handleMouseOver',
        value: function _handleMouseOver(e) {
            this.setState({
                hoveringInfo: true
            });
        }
    }, {
        key: '_handleMouseLeave',
        value: function _handleMouseLeave(e) {
            this.setState({
                hoveringInfo: false
            });
        }
    }]);
    return OptionItem;
}(React.Component);

var MultiPayAccessOptions = function (_React$Component) {
    inherits(MultiPayAccessOptions, _React$Component);

    function MultiPayAccessOptions(props) {
        classCallCheck(this, MultiPayAccessOptions);

        var _this = possibleConstructorReturn(this, (MultiPayAccessOptions.__proto__ || Object.getPrototypeOf(MultiPayAccessOptions)).call(this, props));

        _this.state = {
            activationCode: ''
        };

        _this._onCodeInputChange = _this._onCodeInputChange.bind(_this);
        _this._onSubmitCode = _this._onSubmitCode.bind(_this);
        return _this;
    }

    createClass(MultiPayAccessOptions, [{
        key: 'render',
        value: function render() {
            return React.createElement(
                'div',
                { style: styles$7.optionsWrapper },
                React.createElement(
                    'form',
                    { method: 'POST',
                        action: this.props.endpointUrl,
                        onSubmit: this._onSubmitCode,
                        className: 'nolimit' },
                    React.createElement(OptionItem, {
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
                    })
                ),
                React.createElement(OptionItem, {
                    item: 2,
                    icon: 'https://s3-us-west-2.amazonaws.com/lumen-components-prod/assets/icons/icon-credit-card.png',
                    iconAlt: 'an icon of a credit card',
                    infoIcon: '',
                    infoIconAlt: '',
                    label: 'Pay $' + this._calculateAmount() + ' Activation Fee Online',
                    subLabel: '',
                    buttonText: 'Pay Now',
                    showCheckout: this.props.showCheckout,
                    activationCodeErrors: this.props.activationCodeErrors
                }),
                React.createElement(OptionItem, {
                    item: 3,
                    icon: 'https://s3-us-west-2.amazonaws.com/lumen-components-prod/assets/icons/icon-clock.png',
                    iconAlt: 'an icon of a clock',
                    infoIcon: '',
                    infoIconAlt: '',
                    label: this._getLabelText(),
                    subLabel: 'quiz_count' === this.props.trialType ? this.props.trialPassesRemaining + ' of 2 Passes Available' : '',
                    buttonText: this._getButtonText(),
                    trialPassesRemaining: this.props.trialPassesRemaining,
                    trialTimeRemaining: this.props.trialTimeRemaining,
                    trialType: this.props.trialType,
                    paymentStatus: this.props.paymentStatus,
                    redirectTo: this.props.redirectTo,
                    activationCodeErrors: this.props.activationCodeErrors
                })
            );
        }
    }, {
        key: '_calculateAmount',
        value: function _calculateAmount() {
            var amount = (this.props.chargeAmount / 100).toFixed(2);
            return 0 === amount % 1 ? amount | 0 : amount;
        }
    }, {
        key: '_getLabelText',
        value: function _getLabelText() {
            if ('quiz_count' === this.props.trialType) {
                return 'Use a Free Pass to Access Assessment';
            }

            if ('trial_not_started' === this.props.paymentStatus) {
                return 'Need More Time? Start a Two-week Trial';
            }

            if ('in_trial' === this.props.paymentStatus) {
                return 'Continue Trial, ' + this._getTimeRemaining() + ' Remaining';
            }

            if ('can_extend' === this.props.paymentStatus || 'expired' === this.props.paymentStatus) {
                return 'Trial Expired';
            }
        }
    }, {
        key: '_getTimeRemaining',
        value: function _getTimeRemaining() {
            var timeLeft = this.props.trialTimeRemaining;

            if (60 > timeLeft) {
                timeLeft = 'less than 1 Minute';
            } else if (60 < timeLeft && 120 > timeLeft) {
                timeLeft = '1 minute';
            } else if (3600 >= timeLeft) {
                timeLeft = Math.floor(timeLeft / 60) + ' Minutes';
            } else if (3600 <= timeLeft && 7200 > timeLeft) {
                timeLeft = Math.floor(timeLeft / 3600) + ' Hour';
            } else if (86400 > timeLeft) {
                timeLeft = Math.floor(timeLeft / 3600) + ' Hours';
            } else if (86400 < timeLeft && 172800 > timeLeft) {
                timeLeft = '1 day';
            } else {
                timeLeft = (timeLeft / 86400).toFixed() + ' Days';
            }

            return timeLeft;
        }
    }, {
        key: '_getButtonText',
        value: function _getButtonText() {
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
    }, {
        key: '_onCodeInputChange',
        value: function _onCodeInputChange(code) {
            this.setState({ code: code });
        }
    }, {
        key: '_onSubmitCode',
        value: function _onSubmitCode(e) {
            if (!this.state.code) {
                e.preventDefault();
            }
        }
    }]);
    return MultiPayAccessOptions;
}(React.Component);

var MultiPayCourseAssessmentActivation = function (_React$Component) {
    inherits(MultiPayCourseAssessmentActivation, _React$Component);

    function MultiPayCourseAssessmentActivation(props) {
        classCallCheck(this, MultiPayCourseAssessmentActivation);
        return possibleConstructorReturn(this, (MultiPayCourseAssessmentActivation.__proto__ || Object.getPrototypeOf(MultiPayCourseAssessmentActivation)).call(this, props));
    }

    createClass(MultiPayCourseAssessmentActivation, [{
        key: 'render',
        value: function render() {
            return React.createElement(
                'div',
                { style: styles$6.bodyWrapper },
                React.createElement(
                    'h1',
                    { className: 'activation-heading', style: styles$6.heading },
                    'Course Assessment Activation'
                ),
                React.createElement(
                    'p',
                    null,
                    'To access this and future assessments for this course, select an option below.'
                ),
                React.createElement(
                    'p',
                    null,
                    'Course content is always available and free.'
                ),
                React.createElement(MultiPayAccessOptions, {
                    trialType: this.props.trialType,
                    trialPassesRemaining: this.props.trialPassesRemaining,
                    trialTimeRemaining: this.props.trialTimeRemaining,
                    paymentStatus: this.props.paymentStatus,
                    chargeAmount: this.props.chargeAmount,
                    showCheckout: this.props.showCheckout,
                    redirectTo: this.props.redirectTo,
                    activationCodeErrors: this.props.activationCodeErrors,
                    endpointUrl: this.props.endpointUrl,
                    assessmentUrl: this.props.assessmentUrl
                }),
                React.createElement('div', { style: styles$6.footerBorder }),
                React.createElement(
                    'p',
                    { style: styles$6.footerText },
                    'By clicking on Enter Code, Pay Now, or ',
                    this._renderTrialContinueText(),
                    ' you agree to Lumen Learning\'s ',
                    React.createElement(
                        'a',
                        { target: '_blank', href: 'https://lumenlearning.com/policies/terms-of-service', style: styles$6.footerLinks },
                        'Terms of Service'
                    ),
                    ' and ',
                    React.createElement(
                        'a',
                        { target: '_blank', href: 'https://lumenlearning.com/policies/privacy-policy', style: styles$6.footerLinks },
                        'Privacy Policy'
                    ),
                    '.'
                ),
                this._renderFooterLogo()
            );
        }
    }, {
        key: '_renderTrialContinueText',
        value: function _renderTrialContinueText() {
            if (this.props.trialType === "quiz_count") {
                return "Use Pass";
            } else {
                return this.props.paymentStatus === "in_trial" ? "Continue Trial" : "Start Trial";
            }
        }
    }, {
        key: '_renderFooterLogo',
        value: function _renderFooterLogo() {
            if (this.props.schoolLogoUrl != null && this.props.schoolLogoUrl !== '') {
                return React.createElement(
                    'div',
                    { className: 'lumen-attribution', style: styles$6.lumenAttributionWrapper },
                    React.createElement(
                        'span',
                        null,
                        'Open Courseware by '
                    ),
                    React.createElement(
                        'a',
                        { style: styles$6.lumenLogoLink, href: "https://www.lumenlearning.com", target: '_blank' },
                        React.createElement('img', { src: 'https://s3-us-west-2.amazonaws.com/lumen-components/assets/Lumen-300x138.png',
                            alt: 'Lumen Learning logo', className: 'lumen-logo',
                            style: styles$6.lumenLogo })
                    )
                );
            }
        }
    }]);
    return MultiPayCourseAssessmentActivation;
}(React.Component);

var MultiPayPage = function (_React$Component) {
    inherits(MultiPayPage, _React$Component);

    function MultiPayPage(props) {
        classCallCheck(this, MultiPayPage);

        var _this = possibleConstructorReturn(this, (MultiPayPage.__proto__ || Object.getPrototypeOf(MultiPayPage)).call(this, props));

        _this.state = {
            showCheckout: false
        };

        _this._showCheckout = _this._showCheckout.bind(_this);
        return _this;
    }

    createClass(MultiPayPage, [{
        key: 'render',
        value: function render() {
            return React.createElement(
                'div',
                { style: styles$4.pageWrapper },
                this._renderBanner(),
                React.createElement(
                    'div',
                    { className: 'multipay-page-wrapper', style: styles$4.pageWrapperInner },
                    React.createElement('img', {
                        src: this._getLogoUrl(),
                        alt: this._getLogoAltText(),
                        style: styles$4.logoImg
                    }),
                    React.createElement(
                        'div',
                        null,
                        this._getPageBody()
                    )
                )
            );
        }
    }, {
        key: '_renderBanner',
        value: function _renderBanner() {
            if ('quiz_count' === this.props.trialType && 0 === this.props.trialPassesRemaining || ('can_extend' === this.props.paymentStatus || 'expired' === this.props.paymentStatus) && 0 === this.props.trialTimeRemaining) {
                return React.createElement(Banner, { trialType: this.props.trialType, paymentStatus: this.props.paymentStatus, redirectTo: this.props.redirectTo });
            }
        }
    }, {
        key: '_showCheckout',
        value: function _showCheckout() {
            var showCheckout = !this.state.showCheckout;

            this.setState({
                showCheckout: showCheckout
            });
        }
    }, {
        key: '_getLogoUrl',
        value: function _getLogoUrl() {
            return this.props.schoolLogoUrl || 'https://s3-us-west-2.amazonaws.com/lumen-platform-assets/images/lumen-open-courseware.png';
        }
    }, {
        key: '_getLogoAltText',
        value: function _getLogoAltText() {
            return this.props.institutionName ? this.props.institutionName + ' logo' : 'Lumen Open Courseware logo';
        }
    }, {
        key: '_getPageBody',
        value: function _getPageBody() {
            if (this.state.showCheckout) {
                return React.createElement(CheckoutTaxPage, {
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
                return React.createElement(MultiPayCourseAssessmentActivation, {
                    trialType: this.props.trialType,
                    trialPassesRemaining: this.props.trialPassesRemaining,
                    trialTimeRemaining: this.props.trialTimeRemaining,
                    paymentStatus: this.props.paymentStatus,
                    chargeAmount: this.props.chargeAmount,
                    showCheckout: this._showCheckout,
                    redirectTo: this.props.redirectTo,
                    activationCodeErrors: this.props.activationCodeErrors || [],
                    endpointUrl: this.props.endpointUrl,
                    assessmentUrl: this.props.assessmentUrl,
                    schoolLogoUrl: this.props.schoolLogoUrl
                });
            }
        }
    }]);
    return MultiPayPage;
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
  },
  renderCheckoutTaxPage: function renderCheckoutTaxPage(elementId, props) {
    React.render(React.createElement(CheckoutTaxPage, props), document.getElementById(elementId));
  },
  // Multi Pay
  renderMultiPayPage: function renderMultiPayPage(elementId, props) {
    React.render(React.createElement(MultiPayPage, props), document.getElementById(elementId));
  }
};

return direct_pay_components;

}(React));
