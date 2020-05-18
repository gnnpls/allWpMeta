# Shell script that deploys changes from allWpMeta to filox repository


echo -e "\e[92mPushing to allWpMeta\e[92m"
git add .
git commit -m "AllWpMeta update | Automated script"
git push origin master

echo -e "\e[34mCopying allWpMeta to filox "
cp -r . ../filox/includes/allWpMeta/

cd ..
cd filox

echo -e "\e[92mPushing to filox"
git add .
git commit -m "AllWpMeta update | Automated script"
git push origin master