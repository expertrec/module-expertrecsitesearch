#!/bin/bash -ex
# need version number as parameter

version_number="3.0.5"

script_path=$(readlink -f $(dirname $0))
temp_folder_name="/tmp/release"

cd ${script_path}
rm -rf ${temp_folder_name}
mkdir -p ${temp_folder_name}/ExpertrecSiteSearch
cp -r $(ls) ${temp_folder_name}/ExpertrecSiteSearch/
cd ${temp_folder_name}/ExpertrecSiteSearch/
rm build.sh README.md

sed -i 's/3.0.5/'${version_number}'/g' etc/module.xml
sed -i 's/3.0.5/'${version_number}'/g' composer.json

cd ${temp_folder_name}
zip -r expertrec_expertrecsitesearch-${version_number}.zip ExpertrecSiteSearch

rm -rf ExpertrecSiteSearch
mv * ${script_path}