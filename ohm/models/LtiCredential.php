<?php

namespace OHM\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * LTI domain credentials are stored as user records.
 *
 * This model hides all columns unrelated to LTI domain credentials. Due to how
 * this needs to be done with Eloquent, any newly added columns will show up
 * when returning instances of this model. ($hidden needs to be updated)
 *
 * FIXME: Check for the appearance of new table columns (and the need to
 * FIXME: update $hidden) with acceptance tests.
 *
 * The following column usage/purpose data was obtained by looking at:
 * - admin/forms.php (search for 'listltidomaincred')
 * - admin/actions.php (search for 'modltidomaincred')
 *
 * Column         Purpose
 * ------         -------
 * email       == LTI domain
 * SID         == key
 * rights      == Can create instructors (76 = Yes, else = No)
 * password    == LTI secret
 * groupid     == group id
 * FirstName   == LTI domain (yes, duplicated)
 * LastName    == "LTIcredential" (literal)
 *
 * Class LtiCredential
 * @package OHM\Models
 */
class LtiCredential extends Model
{

	protected $table = "imas_users";
	protected $dateFormat = 'U';

	/**
	 * Disable usage of the "updated_at" column. This column does not currently exist.
	 *
	 * @var string
	 */
	const UPDATED_AT = null;

	/**
	 * Ensure we only get LTI credentials from the "imas_users" table.
	 */
	protected static function boot()
	{
		parent::boot();
		static::addGlobalScope(new LtiCredentialScope());
	}

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['SID', 'email', 'password', 'rights', 'groupid',
		'FirstName', 'LastName'];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'FirstName', 'LastName', 'lastaccess',
		'msgnotify', 'qrightsdef', 'deflib', 'usedeflib', 'homelayout',
		'hasuserimg', 'remoteaccess', 'theme', 'listperpage',
		'hideonpostswidget', 'specialrights', 'FCMtoken', 'jsondata',
		'forcepwreset'];

	public function getAttribute($key)
	{
		if (array_key_exists($prefixedKey = 'pst_' . $key, $this->attributes)) {
			return $this->attributes[$prefixedKey];
		}

		return parent::getAttribute($key);
	}
}
