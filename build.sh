#!/bin/bash -ex
# need version number as parameter

version_number="3.0.4"

script_path=$(readlink -f $(dirname $0))
temp_folder_name="/tmp/release"

cd ${script_path}
rm -rf ${temp_folder_name}
mkdir -p ${temp_folder_name}/Expertrecsitesearch
cp -r $(ls) ${temp_folder_name}/Expertrecsitesearch/
cd ${temp_folder_name}/Expertrecsitesearch/
rm build.sh README.md

sed -i 's/0.0.0/'${version_number}'/g' etc/module.xml
sed -i 's/0.0.0/'${version_number}'/g' composer.json

cd ${temp_folder_name}
zip -r expertrec_expertrecsitesearch-${version_number}.zip Expertrecsitesearch

rm -rf Expertrecsitesearch
mv * ${script_path}