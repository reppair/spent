<?php

namespace App;

enum Role: string
{
    case Owner = 'owner';
    case Member = 'member';
}
