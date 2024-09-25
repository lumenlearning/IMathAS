<?php
require '../init.php';

$stm = $GLOBALS['DBH']->prepare("SELECT FirstName, LastName, email FROM imas_users WHERE id = :userId");
$stm->execute([':userId' => $userid]);
$results = $stm->fetch(PDO::FETCH_ASSOC);

$helpUserFullName = Sanitize::simpleStringWithSpaces($results['FirstName'] . ' ' . $results['LastName']);
$helpUserEmail = Sanitize::emailAddress($results['email']);
$helpCourseId = empty($_GET['cid']) ? '' : Sanitize::onlyInt($_GET['cid']);
?>

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
        <a target="_blank" href="<?php echo $GLOBALS['CFG']['GEN']['FACULTY_USER_GUIDE_URL']; ?>">
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
      <form id="zd-help-form" class="zd-help">
        <div class="zd-info">
          <label for="z_name" class="u-sr-only">Name</label>
          <input type="text" placeholder="Name" name="z_name" id="z_name" class="field pii-full-name" value="<?php echo $helpUserFullName; ?>"/>

          <label for="z_email" class="u-sr-only">Email</label>
          <input type="text" placeholder="Email" name="z_email" id="z_email" class="field pii-email" value="<?php echo $helpUserEmail; ?>"required />

          <label for="z_cid" class="u-sr-only">Course Id</label>
          <input type="text" placeholder="Course ID (optional)" name="z_cid" id="z_cid" class="field" value="<?php echo $helpCourseId; ?>"/>
        </div>

        <label for="z_subject" class="u-sr-only">Subject</label>
        <input type="text" placeholder="Ticket Subject" name="z_subject" id="z_subject" class="field" required />

        <label for="z_description" class="u-sr-only">Description</label>
        <textarea placeholder="How can we help?" name="z_description" id="z_description" rows="6" class="field" required></textarea>
       
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
