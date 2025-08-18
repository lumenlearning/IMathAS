<?php
//IMathAS: Course browser page
//(c) 2017 David Lippman

//Use of course browser requires setting $CFG['coursebrowser'] in your config.php
//to a javascript file in /javascript/.  See coursebrowserprops.js.dist for an
//example.  In that file, you can specify whatever characteristics you want
//provided when promoting a course.
//For a dropdown, specify "options".
//For a multi-select, specify "options" and add "multi": true
//For strings, put "type": "string"
//For textarea, put "type": "textarea"
//For a field you'll fill in (no entry box), put "fixed": true
//use "sortby" with consecutive values to specify order in characteristics
//should be used to sort values. Use negative for a descending sort

require_once "../init.php";
if (!isset($CFG['coursebrowser'])) {
	echo "Course Browser is not enabled on this site";
	exit;
}
$browserprops = json_decode(file_get_contents(__DIR__.'/../javascript/'.$CFG['coursebrowser'], false, null, 25), true);

$action = "redirect";
if (isset($_GET['embedded'])) {
	$action = "framecallback";
	$nologo = true;
	$flexwidth = true;
}

if (($myrights == 100 || ($myspecialrights&32)==32) && isset($_GET['forgrp'])) {
	$dispgroupid = Sanitize::onlyInt($_GET['forgrp']);
} else {
	$dispgroupid = $groupid;
}

/*** Utility functions ***/
function getCourseBrowserJSON() {
  global $DBH, $browserprops, $dispgroupid;

  $stm = $DBH->prepare("SELECT parent FROM imas_groups WHERE id=?");
  $stm->execute(array($dispgroupid));
  $supergroupid = $stm->fetchColumn(0);

  $query = "SELECT ic.id,ic.name,ic.jsondata,iu.FirstName,iu.LastName,ig.name AS groupname,ig.parent,ic.istemplate,iu.groupid ";
  $query .= "FROM imas_courses AS ic JOIN imas_users AS iu ON ic.ownerid=iu.id JOIN imas_groups AS ig ON iu.groupid=ig.id ";
  $query .= "WHERE ic.istemplate > 0 AND ((ic.istemplate&17)>0 OR ((ic.istemplate&2)>0 AND iu.groupid=?)";
  $qarr = array($dispgroupid);
  if ($supergroupid>0) {
  	  $query .= " OR ((ic.istemplate&32)>0 AND ig.parent=?)";
  	  array_push($qarr, $supergroupid);
  }
  $query .= ")";
  $stm = $DBH->prepare($query);
  $stm->execute($qarr);
  $courseinfo = array();
  while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
    $jsondata = json_decode($row['jsondata'], true);
    if (!isset($jsondata['browser'])) {
      $jsondata['browser'] = array();
    }

    $jsondata['browser']['id'] = $row['id'];
    if (empty($jsondata['browser']['owner'])) {
    	    $jsondata['browser']['owner'] = $row['FirstName'].' '. $row['LastName']. ' ('.$row['groupname'].')';
    }
     if (!isset($jsondata['browser']['name'])) {
    	    $jsondata['browser']['name'] = $row['name'];
    }

    if (($row['istemplate']&2)==2 && $row['groupid']==$dispgroupid) { //group template for user's group
    	$jsondata['browser']['coursetype'] = 0;
    } else if (($row['istemplate']&32)==32 && $row['parent']==$supergroupid) { //super-group template for user's group
    	$jsondata['browser']['coursetype'] = 0;
    } else if (($row['istemplate']&1)==1) { //global template
    	$jsondata['browser']['coursetype'] = 1;
    } else {
    	$jsondata['browser']['coursetype'] = 2;
    }
    $courseinfo[] = $jsondata['browser'];
  }
  //Sort courseinfo
  $sortby = array();
  $sortby[0] = array(
  	  'prop'=>'coursetype',
  	  'asc'=>true);
  foreach($browserprops as $propname=>$props) {
  	  if (isset($props['sortby'])) {
  	  	  $loc = abs($props['sortby']);
  	  	  $sortby[$loc] = array();
  	  	  $sortby[$loc]['prop'] = $propname;
  	  	  $sortby[$loc]['asc'] = ($props['sortby']>0);
  	  	  if (isset($props['options'])) {
  	  	  	  $i = 0;
  	  	  	  $orderref = array();
  	  	  	  foreach ($props['options'] as $k=>$v) {
  	  	  	  	  $orderref[$k] = $i;
  	  	  	  	  $i++;
  	  	  	  }
              $orderref['undef'] = $i;
  	  	  	  $sortby[$loc]['ref'] = $orderref;
  	  	  }
  	  }
  }
  ksort($sortby);
  usort($courseinfo, function($a,$b) use ($sortby) {
  	foreach ($sortby as $sortinf) {
  		if ($sortinf['prop']=='id' && $a['coursetype']<2) {
  			$sortinf['prop'] = 'name';
  			$sortinf['asc'] = 1;
  		}
  		if (!isset($a[$sortinf['prop']]) || !isset($b[$sortinf['prop']])) {
  			continue;
  		}
  		$aval = $a[$sortinf['prop']];
  		if (is_array($aval)) { $aval = $aval[0] ?? 'undef';}
  		$bval = $b[$sortinf['prop']];
  		if (is_array($bval)) { $bval = $bval[0] ?? 'undef';}
  		if (isset($sortinf['ref']) && isset($sortinf['ref'][$aval]) && isset($sortinf['ref'][$bval])) {
  			if ($sortinf['ref'][$aval] != $sortinf['ref'][$bval]) {
  				return (($sortinf['ref'][$aval] < $sortinf['ref'][$bval])? -1 : 1)*($sortinf['asc']?1:-1);
  			}
  		} else {
  			if ($aval != $bval) {
  				return (($aval < $bval)? -1 : 1)*($sortinf['asc']?1:-1);
  			}
  		}
  	}
  	return 0;
  });

  return json_encode($courseinfo);
}

/*** Start output ***/
$placeinhead = '<script type="text/javascript">';
$placeinhead .= 'var courses = '.getCourseBrowserJSON().';';
$placeinhead .= 'var courseBrowserAction = "'.Sanitize::simpleString($action).'";';
$placeinhead .= '</script>';
if (!empty($CFG['GEN']['uselocaljs'])) {
	$placeinhead .= '<script type="text/javascript" src="'.$staticroot.'/javascript/vue3-4-31.min.js"></script>';
} else {
    $placeinhead .= '<script src="https://cdnjs.cloudflare.com/ajax/libs/vue/3.4.31/vue.global.prod.min.js" integrity="sha512-Dg9zup8nHc50WBBvFpkEyU0H8QRVZTkiJa/U1a5Pdwf9XdbJj+hZjshorMtLKIg642bh/kb0+EvznGUwq9lQqQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>';
}
$placeinhead .= '<script src="'.$imasroot.'/javascript/'.$CFG['coursebrowser'].'"></script>
	<link rel="stylesheet" href="coursebrowser.css?v=072018" type="text/css" />';

$pagetitle = _('Course Browser');
require_once "../header.php";

if (!isset($_GET['embedded'])) {
  $curBreadcrumb = $breadcrumbbase . _('Course Browser');
  echo '<div class=breadcrumb>'.$curBreadcrumb.'</div>';
	echo '<div id="headercoursebrowser" class="pagetitle"><h1>'.$pagetitle.'</h1></div>';
	echo '<div class="c" style="margin-bottom: 20px;">';
	echo '<a href="courselist.php" class="btn">View All Courses in Database</a>';
	echo '</div>';
}
?>

<div id="app" v-cloak>
	<div class="course-template-message">
		<div v-if="filterType === 1 || (Array.isArray(filterType) && filterType.includes(1))" class="filter-message">
				<h1>Lumen Course Templates</h1>
				<p>Lumen template courses are designed with evidence-based teaching practices, fully scaffolded for students to build a strong foundation in mathematics.</p>
				<p><strong>Each module contains</strong></p>
				<ul>
					<li>Background You'll Need review content</li>
					<li>Lumen OHM Readiness Check</li>
					<li>Learn It pages</li>
					<li>Apply It pages</li>
					<li>Fresh Take pages</li>
					<li>Lumen OHM Self Check</li>
					<li>Lumen OHM Quiz</li>
					<li>Cheat Sheet</li>
					<li>Get Stronger Problems</li>
					<li>Instructor Guide</li>
					<li>PowerPoint</li>
					<li>In-class Instructor Lead Activity</li>
				</ul>
			</div>

			<div v-if="Array.isArray(filterType) && filterType.includes(0) && filterType.includes(2)" class="filter-message">
				<h1>Community Course Templates</h1>
				<p>These are courses shared by faculty members and contributed courses. They are not supported by Lumen and should only be used at your own risk.</p>
				<p><strong>They are not supported by Lumen and should only be used at your own risk.</strong></p>
			</div>
	</div>

	<div class="course-template-filters">
	<div id="filters">
			Filter results:
			<span v-for="propname in propsToFilter" class="dropdown-wrap">
				<button @click="showFilter = (showFilter==propname)?'':propname">
					{{ courseBrowserProps[propname].name }} {{ catprops[propname].length > 0 ? '('+catprops[propname].length+')': '' }}
					<span class="arrow-down2" :class="{rotated: showFilter==propname}"></span>
				</button>
				<transition name="fade" @enter="adjustpos">
					<ul v-if="showFilter == propname" class="filterwrap">
						<li v-if="courseBrowserProps[propname].hasall">
							<span>Show courses that contain <i>all</i> of:</span>
						</li>
						<li v-if="!courseBrowserProps[propname].hasall">
							<span>Show courses that contain <i>any</i> of:</span>
						</li>
						<li v-for="(longname,propval) in courseBrowserProps[propname].options">
							<span v-if="propval.match(/^group/)" class="optgrplabel"><em>{{ longname }}</em></span>
							<label v-else><input type="checkbox" :value="propname+'.'+propval" v-model="selectedItems">
							{{ longname }}</label>
						</li>
					</ul>
				</transition>
			</span>
			<a href="#" @click.prevent="selectedItems = []" v-if="selectedItems.length>0">Clear Filters</a>
		</div>
	</div>

	<div style="position: relative" id="card-deck-wrap">
	<div v-if="filteredCourses.length==0" class="no-matches"><?php echo _('No matches found'); ?></div>
	
	<div v-for="(levelGroup, level) in coursesByLevel" :key="level" class="level-group">
		<div class="level-header">
			<h2>{{ getLevelDisplayName(level) }}</h2>
		</div>
		<transition-group name="fade" tag="div" class="card-deck">
			<div v-for="course in levelGroup" :key="course.id" class="card">
				<div class="card-body">
					<div class="card-header" :class="'coursetype'+course.coursetype" @click="toggleCourse(level + '-' + course.id)" style="cursor: pointer;">
						<b>{{ course.name }}</b>
						<span class="open-close-caret" :class="{ 'expanded': expandedCourses.includes(level + '-' + course.id) }">
							<svg width="15" height="9" viewBox="0 0 15 9" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M13.4121 1L7.41211 7L1.41211 0.999999" stroke="#636566" stroke-width="2" stroke-linecap="round"/>
							</svg>
						</span>
					</div>
					<div class="card-main" v-show="expandedCourses.includes(level + '-' + course.id)">
						<table class="proplist">
						<caption class="sr-only">Course Details</caption>
						<tbody>
						<tr v-for="(propval,propname) in courseOut(course)">
							<th>{{ courseBrowserProps[propname].name }}</th>
							<td v-if="!Array.isArray(propval)"> {{ propval }} </td>
							<td v-if="Array.isArray(propval)">
								<ul class="nomark">
									<li v-for="subprop in propval">
										{{ courseBrowserProps[propname].options[subprop] }}
									</li>
								</ul>
							</td>
						</tr>

						</tbody></table>
						<p v-for="(propval,propname) in courseText(course)"
						class="pre-line"
						>{{ propval }}</p>

						<div class="card-footer">
							<button @click="previewCourse(course.id)">Preview Course</button>
							<button @click="copyCourse(course)">Copy This Course</button>
						</div>
					</div>
					
				</div>
			</div>
		</transition-group>
	</div>

</div>

</div>

<script type="text/javascript">
const { createApp } = Vue;
createApp({
	data: function() {
        return {
            selectedItems: [],
            courseBrowserProps: courseBrowserProps,
            showFilters: false,
            showFilter: '',
            filterLeft: 0,
            courseTypes: courseBrowserProps.meta.courseTypes,
            		activeTab: 0,
		filterType: null,
            expandedCourses: [],
        }
	},
	methods: {
		clickaway: function(event) {
			var dropdowns = document.getElementsByClassName("dropdown-wrap");
			var isClickInside = false;
			for (var i=0;i<dropdowns.length;i++) {
				if (dropdowns[i].contains(event.target)) {
					isClickInside = true;
					break;
				}
			}
			if (!isClickInside) {
				this.showFilter = '';
			}
		},
		courseOut: function (course) {
			var courseout = {};
			for (propname in course) {
				if (this.courseBrowserProps[propname]) {
					if (this.courseBrowserProps[propname].options) {
						if (Array.isArray(course[propname])) {
							courseout[propname] = course[propname];
						} else if (course[propname]=='other') {
							courseout[propname] = course[propname+'other'];
						} else {
							courseout[propname] = this.courseBrowserProps[propname].options[course[propname]];
						}
					} else if (courseBrowserProps[propname].type && courseBrowserProps[propname].type=='string' && propname!='name') {
						courseout[propname] = course[propname];
					}
				}
			}
			return courseout;
		},
        courseText: function (course) {
			var courseout = {};
			for (propname in course) {
				if (this.courseBrowserProps[propname] && 
                    this.courseBrowserProps[propname].type &&
                    this.courseBrowserProps[propname].type == 'textarea') {
							courseout[propname] = course[propname];
				}
			}
			return courseout;
		},
		previewCourse: function (id) {
			window.open("../course/course.php?cid="+id,"_blank");
		},
		copyCourse: function (course) {
			if (courseBrowserAction=="redirect") {
				var url = "forms.php?from=home&action=addcourse";
				url += "&tocopyid="+course.id;
				url += "&tocopyname="+encodeURIComponent(course.name);
				window.location.href = imasroot+"/admin/"+url;
			} else {
				window.parent.setCourse(course);
			}
		},
		adjustpos: function(tgt) {
			var rect = tgt.parentNode.getBoundingClientRect();
			var width = window.innerWidth || document.body.clientWidth;
			var height = window.innerHeight || document.body.clientHeight;
			tgt.style.maxHeight = (height - rect.bottom - 30) + "px";
			if (rect.left + tgt.offsetWidth > width) {
				if (rect.right < tgt.offsetWidth) {
					tgt.style.left = "auto";
					tgt.style.right = "auto";
				} else {
					tgt.style.left = "auto";
					tgt.style.right = "0px";
				}
			} else {
				tgt.style.right = "auto";
				tgt.style.left = "0px";
			}
		},
		getLevelDisplayName: function(level) {
			if (level === 'undefined' || level === 'null' || !level) {
				return 'Other';
			}
			return this.courseBrowserProps.level.options[level] || level;
		},
		toggleCourse: function(courseId) {
			const index = this.expandedCourses.indexOf(courseId);
			if (index > -1) {
				this.expandedCourses.splice(index, 1);
			} else {
				this.expandedCourses.push(courseId);
			}
		}
	},
	computed: {
		propsToFilter: function() {
			var props = [];
			for (prop in this.courseBrowserProps) {
				if (this.courseBrowserProps[prop]["options"]) {
					props.push(prop);
				}
			}
			return props;
		},
		catprops: function() {
			var catarr = {};
			for (prop in this.courseBrowserProps) {
				catarr[prop] = [];
			}
			var parts;
			for (i=0;i<this.selectedItems.length;i++) {
				parts = this.selectedItems[i].split('.');
				catarr[parts[0]].push(parts[1]);
			}
			return catarr;
		},
		activeCourseTypes: function() {
			var activeTypes = [];
			for (type in this.courseTypes) {
				// If we're filtering by specific types, only include those types
				if (this.filterType !== null) {
					if (Array.isArray(this.filterType)) {
						if (this.filterType.indexOf(parseInt(type)) === -1) {
							continue;
						}
					} else {
						if (parseInt(type) != this.filterType) {
							continue;
						}
					}
				}
				
				for (var i=0; i<courses.length; i++) {
					if (courses[i].coursetype == type) {
						activeTypes.push(type);
						break;
					}
				}
			}
			if (activeTypes.indexOf(this.activeTab) === -1) {
				this.activeTab = activeTypes[0];
			}
			return activeTypes;
		},
		useTabs: function () {
			// Don't show tabs if we're filtering by a specific course type
			if (this.filterType !== null) {
				// If filtering by multiple types, we can still show tabs
				if (Array.isArray(this.filterType) && this.filterType.length > 1) {
					return (!!this.courseBrowserProps.meta.courseTypeTabs &&
						this.activeCourseTypes.length>1);
				}
				return false;
			}
			return (!!this.courseBrowserProps.meta.courseTypeTabs &&
				this.activeCourseTypes.length>1);
		},
		filteredCourses: function() {
			var selectedCourses = [];

			var includeCourse = true;
			for (var i=0; i<courses.length; i++) {
				// If filterType is set, only show courses of that type
				if (this.filterType !== null) {
					if (Array.isArray(this.filterType)) {
						// Check if course type is in the array of allowed types
						if (this.filterType.indexOf(courses[i].coursetype) === -1) {
							continue;
						}
					} else {
						// Single filter type (backward compatibility)
						if (courses[i].coursetype != this.filterType) {
							continue;
						}
					}
				}
				// Otherwise, use the normal tab filtering
				if (this.useTabs && courses[i].coursetype != this.activeTab) {
					continue;
				}
				includeCourse = true;
				for (prop in this.courseBrowserProps) {
					if (this.catprops[prop].length==0) {
						//no filters selected, skip it
						continue;
					} else if (!courses[i][prop]) {
						//if prop selected, but course doesn't contain it
						includeCourse = false;
						break;
					} else if (this.courseBrowserProps[prop].hasall) {
						//only include if ALL selected filters are included
						if (!this.catprops[prop].every(function(v) {
							return courses[i][prop].indexOf(v) >= 0;
						})) {
							includeCourse = false;
							break;
						}
					} else if (typeof courses[i][prop] == 'object') {
						//only include if ONE of the filter items is in course
						if (!this.catprops[prop].some(function(v) {
							return courses[i][prop].indexOf(v) >= 0;
						})) {
							includeCourse = false;
							break;
						}
					} else {
						//only include if filter is in course
						if (this.catprops[prop].indexOf(courses[i][prop])==-1) {
							includeCourse = false;
							break;
						}
					}
				}
				if (includeCourse) {
					selectedCourses.push(courses[i]);
				}
			}
			return selectedCourses;
		},
		coursesByLevel: function() {
			var grouped = {};
			var filtered = this.filteredCourses;
			
			for (var i = 0; i < filtered.length; i++) {
				var course = filtered[i];
				var level = course.level;
				
				// Handle cases where level might be an array or undefined
				if (Array.isArray(level)) {
					// If level is an array, add the course to each level group
					for (var j = 0; j < level.length; j++) {
						var singleLevel = level[j];
						if (!grouped[singleLevel]) {
							grouped[singleLevel] = [];
						}
						grouped[singleLevel].push(course);
					}
				} else if (level) {
					// Single level
					if (!grouped[level]) {
						grouped[level] = [];
					}
					grouped[level].push(course);
				} else {
					// No level specified
					if (!grouped['undefined']) {
						grouped['undefined'] = [];
					}
					grouped['undefined'].push(course);
				}
			}
			
			// Sort the levels based on the order defined in courseBrowserProps.level.options
			var sortedGrouped = {};
			var levelOrder = Object.keys(this.courseBrowserProps.level.options);
			
			// Add undefined level at the end
			levelOrder.push('undefined');
			
			levelOrder.forEach(function(level) {
				if (grouped[level]) {
					sortedGrouped[level] = grouped[level];
				}
			});
			
			return sortedGrouped;
		}
	},
	created: function() {
		document.addEventListener('click', this.clickaway);
		
		// Check if we should filter by course type
		const urlParams = new URLSearchParams(window.location.search);
		const filterType = urlParams.get('filtertype');
		if (filterType !== null) {
			// Handle comma-separated filter types
			if (filterType.includes(',')) {
				this.filterType = filterType.split(',').map(t => parseInt(t.trim()));
			} else {
				this.filterType = parseInt(filterType);
			}
			// Set active tab to the first filtered type if it exists
			if (Array.isArray(this.filterType) && this.filterType.length > 0) {
				if (this.courseTypes[this.filterType[0]] !== undefined) {
					this.activeTab = this.filterType[0];
				}
			} else if (this.courseTypes[this.filterType] !== undefined) {
				this.activeTab = this.filterType;
			}
		}
	},
	mounted: function() {
        this.$nextTick(function() {
		    $("#fixedfilters + #card-deck-wrap").css("margin-top", $("#fixedfilters").outerHeight() + 10);
        });
	}

}).mount('#app');
</script>

<?php
require_once "../footer.php";
