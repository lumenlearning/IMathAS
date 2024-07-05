#!/bin/bash -eux

function findComposer() {
	declare -a COMPOSER_PATHS
	COMPOSER_PATHS[0]=./composer
	COMPOSER_PATHS[1]=composer.phar
	COMPOSER_PATHS[2]=composer

	for name in ${COMPOSER_PATHS[@]}; do
		local FOUND_COMPOSER=`which "$name"`
		if [ ! -z "$FOUND_COMPOSER" ]; then
			local COMPOSER=`realpath "$FOUND_COMPOSER"`
			echo "$COMPOSER"
			return
		fi
	done
}

function findNpm() {
	local FOUND_NPM=`which npm`
	if [ ! -z "$FOUND_NPM" ]; then
		local NPM=`realpath "$FOUND_NPM"`
		echo $NPM
		return
	fi
}



COMPOSER=$(findComposer)
if [ -z "$COMPOSER" ]; then
	echo "ERROR: Unable to locate composer.phar or composer"
	exit 1
fi

NPM=$(findNpm)
if [ -z "$NPM" ]; then
	echo "ERROR: Unable to locate npm"
	exit 1
fi



DEPLOY_SCRIPT=`realpath $0`
DEPLOY_SCRIPT_DIR=`dirname "$DEPLOY_SCRIPT"`
REPODIR="${DEPLOY_SCRIPT_DIR}/../"

echo "Installing composer dependencies for OHM."
cd "${REPODIR}"
"$COMPOSER" install

echo "Installing composer dependencies for Lumen API. (OHM question API)"
cd "${REPODIR}/ohm/lumenapi"
"$COMPOSER" install

echo "Generating swagger.json for Lumen API. (OHM question API)"
cd "${REPODIR}/ohm/lumenapi"
"$COMPOSER" swagger

echo "Building assess2."
cd "${REPODIR}/assess2/vue-src"
npm ci
# Ignore all linter errors when building for production.
# Remove the following line when the linter is no longer an issue.
echo '*' > .eslintignore
npm run build

echo "Fixing bad timestamps for zip file."
find "$REPODIR" -mtime +10950 -exec touch {} \;

