# check_rrd
Nagios check script to read and alert on values from rrd files

## Requires
* php5-rrd (=> ```$ apt-get install php5-rrd```)

## Setup
Download check_rrd zip file or clone it into your Nagios's libexec folder (might be /usr/local/nagios/libexec/), then
```$ php composer.phar install```
```$ php composer.phar dump-autoload -o```

## Usage
Nagios configuration:
```
define command {
        command_name    check_rrd
        command_line    /usr/bin/php $USER1$/check_rrd/check_rrd.php $ARG1$ -f $ARG2$
}

define service {
        use                             generic-service
        host_name                       myhost
        service_description             my-service
        check_command                   check_rrd!disk-usage!/path/to/my-file.rrd
}
```

## Checks available
* disk-usage : checks free space as average value for the last hour, emits warning if <20%, alert if <10% (configs/disk-usage.php)
