#!/bin/bash
#
# A sample script to backup the whole wiki site to <wiki>/../backup
# Modify or copy this to match your needs.
#
time (
time=`date +%Y%m%d-%H%M%S`
file=`readlink -f "$0"`
dir=`dirname "$file"`

wiki_dir=$(readlink -f "$dir/../../../../")
wiki_base=$(basename "$wiki_dir")
backup_dir=$(readlink -f "$wiki_dir/../backup")
backup_file="$backup_dir/$wiki_base-$time"

if [ ! -d "$backup_dir" ]; then
    mkdir -p "$backup_dir" &&
    cat >"$backup_dir/.htaccess" <<EOL
Order allow,deny
Deny from all
Satisfy all
EOL
fi &&

tar --exclude=data/cache/[0-9a-f] --exclude=data/locks/[^_]* --exclude=data/tmp/* -jcvf "$backup_file.tar.bz2" -C $(dirname "$wiki_dir") $(basename "$wiki_dir")
)