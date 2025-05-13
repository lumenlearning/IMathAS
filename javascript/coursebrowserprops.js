var courseBrowserProps = {
	"meta": {
		"fixed": true,
		"courseTypes": {
			"0": "Group Template Course",
			"1": "Template Course",
			"2": "Contributed Course"
		}
	},
	"id": {
		"fixed": true,
		"sortby": -2
	}, 
	"name": {
		"name": "Display Name",
		"type": "string",
		"required": true
	},
	"owner": {
		"name": "Contributed by",
		"type": "string"
	},
	"level": {
		"name": "Level",
		"options": {
			"arith": "Arithmetic",
			"prealg": "Prealgebra",
			"elemalg": "Beginning Algebra",
			"intalg": "Intermediate Algebra",
			"mathlit": "Non-STEM Algebra / Math Literacy",
			"colalg": "College Algebra",
			"trig": "Trigonometry",
			"precalc": "Precalculus",
			"calc1": "Calculus I",
			"calc2": "Calculus II",
			"calc3": "Calculus III",
			"business": "Math for Business",
			"qr": "Math for Liberal Arts / Quantitative Reasoning",
			"stats": "Statistics",
			"linalg": "Linear Algebra",
			"diffeq": "Differential Equations",
	                "chem": "Chemistry",
			"acct": "Accounting",
			"acct_financial": "Financial Accounting",
			"acct_managerial": "Managerial Accounting",
			"phys": "Physics",
			"placement": "Placement testing",
			"dana_math_reasoning_foundation": "Dana Center Foundations of Mathematical Reasoning",
			"dana_stats_intro": "Dana Center Introductory Statistics",
			"dana_quantitative_reasoning": "Dana Center Quantitative Reasoning",
			"dana_reasoning1": "Dana Center Reasoning with Functions 1",
			"dana_reasoning2": "Dana Center Reasoning with Functions 2"
		},
		"multi": true,
		"sortby": 1
	},
	"book": {
		"name": "Primary textbook",
		"options": {
			"group1": "Arithmetic / Prealgebra",
			"lmarith": "Arithmetic, MITE/Lippman",
			"sccarith": "Basic Arithmetic Student Workbook, Scottsdale CC",
			"crprea": "Prealgebra, College of the Redwoods",
			"milprea": "Prealgebra, Milano",
            "group2": "Intro / Intermediate Algebra",
            "lumenbegalg": "Beginning Algebra, Lumen Learning",
			"lumenintalg": "Intermediate Algebra, Lumen Learning",
			"sccbega": "Introductory Algebra, Scottsdale CC",
			"ckbega": "Beginning Algebra, CK12/Sousa",
			"wa": "Beginning and Intermediate Algebra, Wallace",
			"sccia": "Intermediate Algebra, Scottsdale CC",
			"group3": "College Algebra / Precalc / Trig",
            "lumencolalg": "College Algebra, Lumen Learning",
			"szcola": "College Algebra, Stitz/Zeager",
			"lrprec": "Precalculus, Lippman/Rasmussen",
			"osprec": "Precalculus, OpenStax",
			"group4": "Calculus",
			"dhcalc": "Contemporary Calculus, Hoffman",
			"group5": "Math for Liberal Arts / QR",
			"lqr": "Math in Society, Lippman",
			"group6": "Statistics",
			"oist": "OpenIntro Statistics, Diaz/Barr/Cetinkaya-Rundel", 
			"kkst": "Statistics Using Technology, Kozak",
			"osst": "Introductory Statistics, OpenStax",
			"group7": "Business Math",
			"lbusprec": "Business Precalculus, Lippman", 
			"chlbuscalc": "Applied Calculus, Calaway/Hoffman/Lippman",
			"group8": "After Calculus",
			"wtdiff": "Elementary Differential Equations, Trench",
			"group9": "Other",
			"other": "Other"
		}
	},
	"mode": {
		"name": "Modality",
		"options": {
			"generic": "Generic, nonspecific",
			"f2f": "Classroom instruction",
			"hybrid": "Hybrid",
			"online": "Fully online",
			"lab": "Emporium"
		}
	},
	"contents": {
		"name": "Contents",
		"options": {
			"FA1": "Formative Assessments (homework, ~1 per week or chapter)",
			"FA2": "Formative Assessments (homework, ~1 per day or section)",
			"SA": "Summative Assessments (online quizzes or exams)",
			"V": "Video lists or video lessons",
			"TL": "Textbook in Lumen Editable Text format",
			"TP": "Textbook in PDF format",
			"TW": "Textbook in Word format",
			"B": "Textbook files or links",
			"PP": "PowerPoint slides",
			"WS": "Worksheets or activities",
			"PA": "Sample paper assessments",
			"I": "Instructor planning resources"
		},
		"multi": true,
		"hasall": true
	},
	"descrip": {
		"name": "Description",
		"subname": "Describe the topics covered, the approach, and what makes this course unique",
		"type": "textarea",
		"required": true
	}
}
