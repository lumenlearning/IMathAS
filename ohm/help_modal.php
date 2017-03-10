<?php require '../config.php'; ?>

<div class="modal-inner">

  <!-- Community Support -->
  <div class="modal-inner-columns" id="community-column">
    <div id="community-inner-column">
      <h2>Ask the Community</h2>
      <div id="community-icon" class="ask-icons"></div>
      <p>
        The community lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
      </p>
      <div id="community-button-container">
        <a href="#"><button id="community-button">Community Forums</button></a>
      </div>
    </div>
    <div id="guides-inner-column">
      <h2>User Guides</h2>
      <div id="guides-icon" class="ask-icons"></div>
      <p>
         The User Guides lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
      </p>
      <div id="guides-button-container">
        <a href="#"><button id="guides-button">User Guides</button></a>
      </div>
    </div>
  </div>

  <!-- Lumen Support -->
  <div class="modal-inner-columns" id="lumen-column">
    <h2>Ask Lumen</h2>
    <div id="lumen-icon" class="ask-icons"></div>

    <div id="ticket_form">
      <form id="zd-help-form">
        <input type="text" placeholder="ticket subject" name="z_subject" id="z_subject" class="field" required />
        <textarea placeholder="how can we help?" name="z_description" id="z_description" rows="6" class="field" required></textarea>
        <input type="text" placeholder="your email address" name="z_email" id="z_email" class="field" required />
        <select name="z_priority" id="z_priority" required>
          <option value="" disabled selected>priority</option>
          <option value="low">Low - First reply within 1 business days. Request solved in 10 business days.</option>
          <option value="normal">Normal - First reply within 1 business days. Request solved in 5 business days.</option>
          <option value="high">High - First reply within 3 hours. Request solved in 2 business days.</option>
          <option value="urgent">Urgent/Service Outage - Call (971)303-8980</option>
        </select>
        <div id="lumen-button-container">
          <button id="lumen-button" type="submit">Submit</button>
        </div>
      </form>
    </div>

    <div id="submission-response"></div>

  </div><!-- End Lumen Support -->
</div><!-- End Modal Inner -->

<script src="<?php echo $imasroot; ?>/ohm/js/zdHelpAjax.js" type="text/javascript"></script>
