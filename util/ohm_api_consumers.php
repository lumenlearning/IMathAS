<?php

require_once(__DIR__ . '/../init.php');
require_once("../header.php");
require_once(__DIR__ . '/../vendor/autoload.php');

use Ramsey\Uuid\Uuid;
use Firebase\JWT\JWT;

?>
    <div class="breadcrumb">
		<?php echo $breadcrumbbase; ?>
        <a href="../admin/admin2.php">Admin</a> &gt;
        <a href="?">Manage API consumers</a>
    </div>
<?php


switch ($_REQUEST['action']) {
	case "index":
		list_groups();
		break;
	case "modify_form":
		modify_form('modify');
		break;
	case "modify":
		modify();
		break;
	case "generate_api_token":
		generate_api_token();
		break;
	case "delete":
		delete();
		break;
	case "create_form":
		modify_form('create');
		break;
	case "create":
		create();
		break;
	default:
		list_groups();
		break;
}

exit;


function list_groups()
{
	global $DBH;

	?>
    <h1>OHM API Consumers</h1>

    <p>
        <a href="?action=create_form">Create new API consumer</a>
    </p>

    <table class="gb">
    <thead>
    <tr>
        <th>Consumer Name</th>
        <th>Description</th>
        <th colspan="3">Actions</th>
    </tr>
    </thead>
    <tbody>
	<?php

	$stm = $DBH->query("SELECT * FROM ohm_api_consumers ORDER BY name");
	$stm->execute();

	$alt = 1;
	while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
		if ($alt == 0) {
			echo "<tr class=\"even\">";
			$alt = 1;
		} else {
			echo "<tr class=\"odd\">";
			$alt = 0;
		}

		$confirmJs = sprintf('onClick="return confirm(\'Are you sure you want to delete %s?\')"',
			Sanitize::encodeStringForDisplay($row['name']));

		$generateTokenLink = sprintf('<a href="?action=generate_api_token&id=%s">Create API Token</a>',
			$row['id']);
		$modifyLink = sprintf('<a href="?action=modify_form&id=%s">Modify</a>', $row['id']);
		$deleteLink = sprintf(
			'<a href="?action=delete&id=%s" %s>Delete</a>',
			$row['id'],
			$confirmJs
		);

		printf("<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td>\n",
			Sanitize::encodeStringForDisplay($row['name']),
			Sanitize::encodeStringForDisplay($row['description']),
			$generateTokenLink,
			$modifyLink,
			$deleteLink
		);
		echo "</tr>\n";
	}
}

function modify()
{
	global $DBH;

	$id = Sanitize::simpleString($_REQUEST['id']);
	$name = Sanitize::simpleStringWithSpaces($_REQUEST['name']);
	$description = Sanitize::simpleStringWithSpaces($_REQUEST['description']);

	$stm = $DBH->prepare(
		'UPDATE ohm_api_consumers SET name = :name, description = :description WHERE id = :id'
	);
	$stm->execute([':id' => $id, ':name' => $name, ':description' => $description]);

	?>
    <h1>Modify API consumer: <?php echo $name; ?></h1>

    <p>Done!</p>

    <p>
        <a href="?">&lt;&lt; Return to API consumer listing</a>
    </p>
	<?php
}

function generate_api_token()
{
	global $DBH;

	$id = Sanitize::simpleString($_REQUEST['id']);

	$stm = $DBH->prepare(
		'SELECT name FROM ohm_api_consumers WHERE id = :id'
	);
	$stm->execute([':id' => $id]);
	$row = $stm->fetch(PDO::FETCH_ASSOC);

	$name = Sanitize::encodeStringForDisplay($row['name']);

	$jwtSecret = getenv('OHM_API_JWT_SECRET') ?
		getenv('OHM_API_JWT_SECRET') : 'development_jwt_secret';
	$jwt = JWT::encode([
		"iss" => "ohm-api",
		"aud" => $id,
		"iat" => time()
	], $jwtSecret, 'HS512');

	?>
    <h1>Generate API token for: <?php echo $name; ?></h1>

    <textarea cols="100" rows="10"><?php echo $jwt; ?></textarea>

    <p>
        <a href="?">&lt;&lt; Return to API consumer listing</a>
    </p>
	<?php
}

function delete()
{
	global $DBH;

	$id = Sanitize::simpleString($_REQUEST['id']);

	$stm = $DBH->prepare("SELECT * FROM ohm_api_consumers WHERE id = :id");
	$stm->execute([':id' => $id]);
	$rowBefore = $stm->fetch(PDO::FETCH_ASSOC);

	$name = Sanitize::encodeStringForDisplay($rowBefore['name']);

	$stm = $DBH->prepare("DELETE FROM ohm_api_consumers WHERE id = :id");
	$stm->execute([':id' => $id]);

	?>
    <h1>Deleted API consumer: <?php echo $name; ?></h1>

    <p>
        <a href="?">&lt;&lt; Return to API consumer listing</a>
    </p>
	<?php
}

function create()
{
	global $DBH;

	$id = Uuid::uuid4();
	$name = Sanitize::simpleStringWithSpaces($_REQUEST['name']);
	$description = Sanitize::simpleStringWithSpaces($_REQUEST['description']);

	$stm = $DBH->prepare(
		'INSERT INTO ohm_api_consumers (id, name, description, created_at, updated_at) '
		. ' VALUES (:id, :name, :description, :created_at, :updated_at)'
	);
	$stm->execute([
		':id' => $id,
		':name' => $name,
		':description' => $description,
		':created_at' => time(),
		':updated_at' => time(),
	]);

	?>
    <h1>Create API consumer: <?php echo $name; ?></h1>

    <p>Done!</p>

    <p>
        <a href="?">&lt;&lt; Return to API consumer listing</a>
    </p>
	<?php
}

function modify_form($type)
{
	global $DBH;

	$type = Sanitize::simpleString($type);
	$id = null;
	$name = null;
	$description = null;

	if ('modify' == $type) {
		$id = Sanitize::simpleString($_REQUEST['id']);
		$stm = $DBH->prepare('SELECT * FROM ohm_api_consumers WHERE id = :id');
		$stm->execute([':id' => $id]);
		$consumer = $stm->fetch(PDO::FETCH_ASSOC);
		$name = Sanitize::encodeStringForDisplay($consumer['name']);
		$description = Sanitize::encodeStringForDisplay($consumer['description']);
	}

	if ('create' == $type) {
		$showId = false;
		echo "<h1>Create API Consumer</h1>\n";
	} elseif ('modify' == $type) {
		$showId = true;
		echo "<h1>Modify API Consumer</h1>\n";
	}
	?>

    <form method="post" action="?action=<?php echo $type; ?>">
        <input type="hidden" name="id" value="<?php echo $id; ?>"/>
		<?php if ($showId) { ?>
            <div style="margin-bottom: 1em;">
                <p>ID: <?php echo $id; ?></p>
            </div>
		<?php } ?>
        <div>
            <label for="name">Name</label>
            <input type="text" id="name" name="name" size="63"
                   value="<?php echo $name; ?>"/>
        </div>
        <div>
            <label for="description">Description</label>
            <input id="description" name="description" size="60"
                   value="<?php echo $description ?>"/>
        </div>
        <div>
            <input type="submit" value=" <?php echo ucfirst($type); ?>"/>
        </div>
    </form>

	<?php
}


