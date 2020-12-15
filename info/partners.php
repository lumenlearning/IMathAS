<?php
$nologo = true;
require("../init_without_validate.php");
$placeinhead = "<link rel=\"stylesheet\" href=\"$imasroot/infopages.css\" type=\"text/css\">\n";
require("../header.php");
$pagetitle ="Contributing Partners";
require("../infoheader.php");
?>

<img class="floatleft" src="<?php echo $imasroot;?>/img/hands.jpg"/>

<div class="content">
<h1>MyOpenMath Contributing Partners</h1>

<h2>What is MyOpenMath?</h2>
<p>MyOpenMath is an online course management and assessment system for mathematics and other quantitative fields.  
MyOpenMath's focus is providing rich algorithmically generated assessment to support the use of free, open textbooks.  
MyOpenMath provides assessment capabilities not available in traditional LMSs.</p>

<p>By using MyOpenMath instead of an expensive publisher-provided homework system, 
faculty can save students $100+ each on course materials and ensure they all have access 
to the required materials, promoting equity and student success.</p>

<h2>What is a Contributing Partner?</h2>
<p>MyOpenMath is a non-profit, and relies on sponsorship and membership fees from 
Contributing Partners to develop and host the site.  By becoming a Contributing Partner, 
your school is helping ensure MyOpenMath can improve and continue to provide a 
high-quality learning system.</p>

<p>Membership fees are $1/student using MyOpenMath when using our standard contract.</p>

<p>If your school requires a custom contract or other paperwork, email sales@myopenmath.com 
with a copy before signing up.  In most cases we can accommodate these at a 
higher membership fee of $3/student.</p>

<h2>How is MyOpenMath different?</h2>
<p>There are a number of online math homework options out there now.  In addition to the 
classic publisher-run systems, there are now a number of commercial companies that provide 
homework options for open textbooks.  The main differences with MyOpenMath are:</p>
<ul>
<li>MyOpenMath is non-profit.</li>
<li>MyOpenMath acts as a collaborative platform, rather than as a pre-made content 
provider.  Questions and courses on MyOpenMath are created and shared by faculty members, 
and faculty can build off their colleague’s work.</li>
<li>MyOpenMath relies on community peer-to-peer support, and does not provide direct 
student or faculty support.  While this steepens the learning curve, it is part of what 
allows us to provide the site at such low cost.</li>
</ul>

<h2>What are the benefits of becoming a Contributing Partner?</h2>
<p>The primary benefit is knowing that your school is doing its part to make a service 
it relies on sustainable.  We also offer these perks for members:</p>
<ul>
<li>Listing as a contributing partner on the website.</li>
<li>A signed contract encompassing our Privacy Policy and Terms of Use, 
  and providing a 99.9% uptime service level agreement.</li>
<li>Priority consideration for technical issues and feature requests.</li>
<li>Special account privileges for administrators or department leads so they can 
  create new instructor accounts, assist colleagues with their courses, and designate 
  school-specific template courses.</li>
<li>School-wide LTI (Learning Tools Interoperability) credentials for LMS integration.</li>
</ul>

<button class="primary" onclick="this.style.display='none';document.getElementById('smart-button-container').style.display='block';">
  Sign Up Now
</button>
<style>
#smart-button-container {
    max-width: 400px;
}
#smart-button-container div {
    margin-top: 0;
    margin-right: 0;
    padding: 0;
}
</style>
<script>
function memtypechange() {
    var memtype = $("#item-options").val();
    $("#contract-warn").toggle(memtype == 'Custom contract');
}
</script>
<div id="smart-button-container" style="display:none">
  <h2>Become a Contributing Partner School</h2>
  <p>
    <label for="schoolname">College or School District</label>
    <input id="schoolname" style="width:100%" />
  </p>
  <p>
    <label for="faculty">Faculty primary contact (name and email)</label>
    <input id="faculty" style="width:100%"/>
  </p>
  <p>
    <label for="admin">Admin contact for contracts (name and email)</label>
    <input id="admin" style="width:100%"/>
  </p>
  <p>The membership fee is based on the estimated number of students who you expect will
    use MyOpenMath during the academic year.</p>
  <p>
    <label for="quantitySelect">Estimated number of students:</label>
    <input type=number size=5 min=1 max=100000 value="100" id="quantitySelect" style="display:inline-block">
  </p>
  <p>
    <label for="item-options">Membership type:</label>
    <select id="item-options" onchange="memtypechange()">
      <option value="Standard contract" price="1">Standard contract - 1 USD/student</option>
      <option value="Custom contract" price="3">Custom contract - 3 USD/student</option>
    </select>
  </p>
  <p id="contract-warn" class="noticetext" style="display:none">If you require a custom contact, be sure to contact us at 
    sales@myopenmath.com before paying to ensure we can accomodate your contract terms.</p>
  <div id="paypal-button-container"></div>
</div>
<div id="payment-thanks" style="display:none">
<h2 class="noticetext">Thank you for becoming a Contributing Partner!</h2>
<p>Within the next week we will send contracts to the admin contact, and follow up 
   with the faculty contact about enabling special account privileges. If you need  
   school-wide LTI credentials, please have your LMS administrator contact
   support@myopenmath.com</p>
</div>
<script src="https://www.paypal.com/sdk/js?client-id=ASNBWqsKfw2hvRkc4dodfIlsruXh9vWdkQgThs_QuNdgXWA-8IIf2qV7vts3hVrVNh4LrKZgWna2L7P3&currency=USD"></script>
<script>
  function initPayPalButton() {
    var shipping = 0;
    var itemOptions = document.querySelector("#smart-button-container #item-options");
    var quantityInput = document.querySelector("#smart-button-container #quantitySelect");

    var orderDescription = 'MyOpenMath Contributing Partner Membership';
    
    paypal.Buttons({
      style: {
        shape: 'rect',
        color: 'white',
        layout: 'vertical',
        label: 'paypal'
      },
      createOrder: function(data, actions) {
        var selectedItemDescription = itemOptions.options[itemOptions.selectedIndex].value;
        var selectedItemPrice = parseFloat(itemOptions.options[itemOptions.selectedIndex].getAttribute("price"));
        var tax = (0 === 0) ? 0 : (selectedItemPrice * (parseFloat(0)/100));
        var quantity = parseInt(quantityInput.value);
        

        tax *= quantity;
        tax = Math.round(tax * 100) / 100;
        var priceTotal = quantity * selectedItemPrice + parseFloat(shipping) + tax;
        priceTotal = Math.round(priceTotal * 100) / 100;
        var itemTotalValue = Math.round((selectedItemPrice * quantity) * 100) / 100;

        return actions.order.create({
          purchase_units: [{
            description: orderDescription,
            amount: {
              currency_code: 'USD',
              value: priceTotal,
              breakdown: {
                item_total: {
                  currency_code: 'USD',
                  value: itemTotalValue,
                },
                shipping: {
                  currency_code: 'USD',
                  value: shipping,
                },
                tax_total: {
                  currency_code: 'USD',
                  value: tax,
                }
              }
            },
            items: [{
              name: selectedItemDescription,
              unit_amount: {
                currency_code: 'USD',
                value: selectedItemPrice,
              },
              quantity: quantity
            }],
          }],
          application_context: {
            shipping_preference: 'NO_SHIPPING'
          }
        });
      },
      onApprove: function(data, actions) {
        return actions.order.capture().then(function(details) {
          var fields = ['schoolname', 'faculty' ]
          $.ajax({
              method: "POST",
              url: '/myopenmath/partnerjoin.php',
              data: {
                schoolname: $("#schoolname").val(),
                faculty: $("#faculty").val(),
                admin: $("#admin").val(),
                stus: $("#quantitySelect").val(),
                memtype: $("#item-options").val(),
                details: JSON.stringify(details)
              },
              dataType: "text"
          }).always(function(msg) {
            $("#smart-button-container").hide();
            $("#payment-thanks").show();
          });
        });
      },
      onError: function(err) {
        console.log(err);
      },
    }).render('#paypal-button-container');
  }
  initPayPalButton();
    </script>


</div>

<p>&nbsp;</p>
<p>&nbsp;</p>
</body>
</html>
