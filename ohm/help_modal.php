<?php require '../init_without_validate.php'; ?>

<div class="modal-inner">

  <!-- Community Support -->
  <div class="modal-inner-columns" id="community-column">
    <div id="community-inner-column">
      <h2>Ask the Community</h2>
      <div id="community-icon" class="ask-icons"></div>
      <p>
        Connect with other faculty to find out how they use OHM on their campus with their students.
      </p>
      <div id="community-button-container">
        <a target="_blank" href="<?php echo $CFG['GEN']['communityforumlink']; ?>" style="text-decoration: none;">
          <button id="community-button" class="modal-buttons">
            Community Forums <img class="new-tab-icon" src="<?php echo $imasroot; ?>/ohm/img/new-tab-icon.png" alt="new tab icon">
          </button>
        </a>
      </div>
    </div>
    <div id="guides-inner-column">
      <h2>User Guides</h2>
      <div id="guides-icon" class="ask-icons"></div>
      <p>
        The OHM Faculty User Guide provides practical how-to information about using OHM and integrating OHM into your Learning Management System.
      </p>
      <div id="guides-button-container">
        <a target="_blank" href="https://lumenlearning.zendesk.com/hc/en-us/categories/115000706447/">
          <button id="guides-button" class="modal-buttons">
            User Guides <img class="new-tab-icon" src="<?php echo $imasroot; ?>/ohm/img/new-tab-icon.png" alt="new tab icon">
          </button>
        </a>
      </div>
    </div>
  </div>

  <!-- Lumen Support -->
  <div class="modal-inner-columns" id="lumen-column">
    <h2>Ask Lumen</h2>
    <div id="lumen-icon" class="ask-icons"></div>
    <p>
      Get personal assistance from Lumen's support team.
    </p>
    <div id="ticket_form">
      <form id="zd-help-form">
        <input type="text" placeholder="ticket subject" name="z_subject" id="z_subject" class="field" required />
        <textarea placeholder="how can we help?" name="z_description" id="z_description" rows="6" class="field" required></textarea>
        <input type="text" placeholder="your email address" name="z_email" id="z_email" class="field" required />
        <select name="z_priority" id="z_priority" required>
          <option value="" disabled selected>priority</option>
          <option value="low">Low</option>
          <option value="normal">Normal</option>
          <option value="high">High</option>
          <option value="urgent">Urgent</option>
        </select>
        <div id="lumen-button-container">
          <button id="lumen-button" type="submit">Submit</button>
        </div>
      </form>
    </div>

    <div id="submission-response"></div>

  </div><!-- End Lumen Support -->
</div><!-- End Modal Inner -->

<script type="text/javascript">
  window.DEFAULT_SETTINGS = {
    imasroot: '<?php echo $imasroot; ?>'
  }
</script>
<script src="<?php echo $imasroot; ?>/ohm/js/zdHelpAjax.js" type="text/javascript"></script>
