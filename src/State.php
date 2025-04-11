<?php

namespace RayanLevert\Model;

/** Different states of an object associated to a database table */
enum State: int
{
    /** Objects that have been just created, not stocked in the database yet */
    case TRANSIANT = 1;

    /** Objects that have been saved in database, or fetched from it */
    case PERSISTENT = 2;

    /** Objects that have been deleted from database */
    case DETACHED = 4;
}
