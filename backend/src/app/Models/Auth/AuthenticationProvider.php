<?php

namespace Izumi\Backend\app\Models\Auth;

enum AuthenticationProvider: string
{
    case Password = 'password';
    case OpenIDConnect = 'openid_connect';
}
