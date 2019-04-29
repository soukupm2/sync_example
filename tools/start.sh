set -e

scriptDir=$(dirname -- "$(readlink -f -- "$BASH_SOURCE")")
cd "$scriptDir"
cd ".."

composer install
yarn install
yarn production