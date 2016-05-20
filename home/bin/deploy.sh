#!/bin/bash
SCRIPT_DIR=$( cd $( dirname ${BASH_SOURCE[0]} ) && pwd )

DEPLOY_PATH="/home/work/www/ice.tec-inf.com"

ROOT_PATH="$SCRIPT_DIR/.."

if [ ! -d $DEPLOY_PATH ] ;
then
    mkdir -p $DEPLOY_PATH
fi

if [ ! -d $DEPLOY_PATH/var ] ;
then
    mkdir -p $DEPLOY_PATH/var
fi

cd $ROOT_PATH

if [ "x$1" = "xupdate" ] ;
then
    composer update
fi

# 不考虑部署原子性问题
rm -rf $DEPLOY_PATH/src
rm -rf $DEPLOY_PATH/vendor
cp -rf $ROOT_PATH/src $DEPLOY_PATH/src
cp -rf $ROOT_PATH/vendor $DEPLOY_PATH/vendor
cp -rf $ROOT_PATH/class_alias.php $DEPLOY_PATH/class_alias.php