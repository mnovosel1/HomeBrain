#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && cd .. && pwd )"


if [ ! -f $DIR/var/hbrain.db ]; then
  if [ -f $DIR/var_sav/hbrain.db ]; then
    cp $DIR/var_sav/hbrain.db $DIR/var/hbrain.db
  else
    sqlite3 $DIR/var/hbrain.db -init $DIR/hbrain.sql &
  fi
fi