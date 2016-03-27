<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18/01/2016
 * Time: 10:56 PM
 */

namespace GroupBot\Brains\IdleRPG;


class IdleBot
{
    private $version = "0.1";

    private $onchan; # users on game channel
    private $rps; # role-players

    //$quest_start = time() + mt_rand(21600);
    private $quest = array(
        'questers' => [],
        'p1' => [], # point 1 for q2
        'p2' => [], # point 2 for q2
        'qtime' => 0, # first quest starts in <=6 hours
        'text' => "",
        'type' => 1,
        'stage' => 1); # quest info

    private $rpreport = 0;       # constant for reporting top players
    private $prev_online;        # user@hosts online on restart, die
    private $auto_login;         # users to automatically log back on
    private $bans;               # bans auto-set by the bot, saved to be removed after 1 hour
    private $pausemode = 0;      # pausemode on/off flag
    private $silentmode = 0;     # silent mode 0/1/2/3, see head of file
    private $queue;              # outgoing message queue
    private $registrations = 0;  # count of registrations this period
    private $sel;                # IO::Select object
    private $lasttime = 1;       # last time that rpcheck() was run
    private $split;              # holds nick!user@hosts for clients that have been netsplit
    private $freemessages = 4;   # number of "free" privmsgs we can send. 0..$freemessages

    public function __construct()
    {
        $this->readconfig();
    }

    public function parse()
    {
                    $lastreg = time();
                    $rps{$arg[4]}{next} = $opts{rpbase};
                    $rps{$arg[4]}{class} = "@arg[6..$#arg]";
                            $rps{$arg[4]}{level} = 0;
                            $rps{$arg[4]}{online} = 1;
                            $rps{$arg[4]}{nick} = $usernick;
                            $rps{$arg[4]}{userhost} = $arg[0];
                            $rps{$arg[4]}{created} = time();
                            $rps{$arg[4]}{lastlogin} = time();
                            $rps{$arg[4]}{pass} = crypt($arg[5],mksalt());
                            $rps{$arg[4]}{x} = int(rand($opts{mapx}));
                            $rps{$arg[4]}{y} = int(rand($opts{mapy}));
                            $rps{$arg[4]}{alignment}="n";
                            $rps{$arg[4]}{isadmin} = 0;
                            for my $item ("ring","amulet","charm","weapon","helm",
                                "tunic","pair of gloves","shield",
                                "set of leggings","pair of boots") {
                            $rps{$arg[4]}{item}{$item} = 0;
                            }
                            for my $pen ("pen_mesg","pen_nick","pen_part",
                                "pen_kick","pen_quit","pen_quest",
                                "pen_logout","pen_logout") {
                            $rps{$arg[4]}{$pen} = 0;
                            }
                            chanmsg("Welcome $usernick\'s new player $arg[4], the ".
                                "@arg[6..$#arg]! Next level in ".
                                duration($opts{rpbase}).".");
                            privmsg("Success! Account $arg[4] created. You have ".
                                "$opts{rpbase} seconds idleness until you ".
                                "reach level 1. ", $usernick);
                            privmsg("NOTE: The point of the game is to see who ".
                                "can idle the longest. As such, talking in ".
                                "the channel, parting, quitting, and changing ".
                                "nicks all penalize you.",$usernick);
                            if ($opts{phonehome}) {
                                my $tempsock = IO::Socket::INET->new(PeerAddr=>
                                    "jotun.ultrazone.org:80");
                                if ($tempsock) {
                                    print $tempsock
                                        "GET /g7/count.php?new=1 HTTP/1.1\r\n".
                                        "Host: jotun.ultrazone.org:80\r\n\r\n";
                                    sleep(1);
                                    close($tempsock);
                                }
                            }
                        }
                    }
                }
                elsif ($arg[3] eq "delold") {
            if (!ha($username)) {
                privmsg("You don't have access to DELOLD.", $usernick);
            }
            # insure it is a number
            elsif ($arg[4] !~ /^[\d\.]+$/) {
                privmsg("Try: DELOLD <# of days>", $usernick, 1);
            }
                    else {
                my @oldaccounts = grep { (time()-$rps{$_}{lastlogin}) >
                ($arg[4] * 86400) &&
                !$rps{$_}{online} } keys(%rps);
                        delete(@rps{@oldaccounts});
                        chanmsg(scalar(@oldaccounts)." accounts not accessed in ".
                            "the last $arg[4] days removed by $arg[0].");
                    }
                }
                elsif ($arg[3] eq "del") {
            if (!ha($username)) {
                privmsg("You don't have access to DEL.", $usernick);
            }
            elsif (!defined($arg[4])) {
            privmsg("Try: DEL <char name>", $usernick, 1);
        }
            elsif (!exists($rps{$arg[4]})) {
            privmsg("No such account $arg[4].", $usernick, 1);
        }
        else {
                delete($rps{$arg[4]});
                chanmsg("Account $arg[4] removed by $arg[0].");
            }
        }
                elsif ($arg[3] eq "mkadmin") {
            if (!ha($username) || ($opts{owneraddonly} &&
                    $opts{owner} ne $username)) {
                privmsg("You don't have access to MKADMIN.", $usernick);
            }
                    elsif (!defined($arg[4])) {
                    privmsg("Try: MKADMIN <char name>", $usernick, 1);
                    }
                    elsif (!exists($rps{$arg[4]})) {
                    privmsg("No such account $arg[4].", $usernick, 1);
                    }
                    else {
                $rps{$arg[4]}{isadmin}=1;
                privmsg("Account $arg[4] is now a bot admin.",$usernick, 1);
            }
                }
                elsif ($arg[3] eq "deladmin") {
            if (!ha($username) || ($opts{ownerdelonly} &&
                    $opts{owner} ne $username)) {
                privmsg("You don't have access to DELADMIN.", $usernick);
            }
                    elsif (!defined($arg[4])) {
                    privmsg("Try: DELADMIN <char name>", $usernick, 1);
                    }
                    elsif (!exists($rps{$arg[4]})) {
                    privmsg("No such account $arg[4].", $usernick, 1);
                    }
                    elsif ($arg[4] eq $opts{owner}) {
                privmsg("Cannot DELADMIN owner account.", $usernick, 1);
            }
                    else {
                $rps{$arg[4]}{isadmin}=0;
                privmsg("Account $arg[4] is no longer a bot admin.",
                    $usernick, 1);
            }
                }
                elsif ($arg[3] eq "hog") {
            if (!ha($username)) {
                privmsg("You don't have access to HOG.", $usernick);
            }
            else {
                chanmsg("$usernick has summoned the Hand of God.");
                hog();
            }
        }
                elsif ($arg[3] eq "rehash") {
            if (!ha($username)) {
                privmsg("You don't have access to REHASH.", $usernick);
            }
            else {
                readconfig();
                privmsg("Reread config file.",$usernick,1);
                $opts{botchan} =~ s/ .*//; # strip channel key if present
                    }
        }
                elsif ($arg[3] eq "chpass") {
            if (!ha($username)) {
                privmsg("You don't have access to CHPASS.", $usernick);
            }
            elsif (!defined($arg[5])) {
            privmsg("Try: CHPASS <char name> <new pass>", $usernick, 1);
        }
            elsif (!exists($rps{$arg[4]})) {
            privmsg("No such username $arg[4].", $usernick, 1);
        }
        else {
                $rps{$arg[4]}{pass} = crypt($arg[5],mksalt());
                privmsg("Password for $arg[4] changed.", $usernick, 1);
            }
        }
                elsif ($arg[3] eq "chuser") {
            if (!ha($username)) {
                privmsg("You don't have access to CHUSER.", $usernick);
            }
            elsif (!defined($arg[5])) {
            privmsg("Try: CHUSER <char name> <new char name>",
                $usernick, 1);
        }
            elsif (!exists($rps{$arg[4]})) {
            privmsg("No such username $arg[4].", $usernick, 1);
        }
            elsif (exists($rps{$arg[5]})) {
            privmsg("Username $arg[5] is already taken.", $usernick,1);
        }
        else {
                $rps{$arg[5]} = delete($rps{$arg[4]});
                privmsg("Username for $arg[4] changed to $arg[5].",
                    $usernick, 1);
            }
        }
                elsif ($arg[3] eq "chclass") {
            if (!ha($username)) {
                privmsg("You don't have access to CHCLASS.", $usernick);
            }
            elsif (!defined($arg[5])) {
            privmsg("Try: CHCLASS <char name> <new char class>",
                $usernick, 1);
        }
            elsif (!exists($rps{$arg[4]})) {
            privmsg("No such username $arg[4].", $usernick, 1);
        }
        else {
                $rps{$arg[4]}{class} = "@arg[5..$#arg]";
                        privmsg("Class for $arg[4] changed to @arg[5..$#arg].",
                            $usernick, 1);
                    }
        }
                elsif ($arg[3] eq "push") {
            if (!ha($username)) {
                privmsg("You don't have access to PUSH.", $usernick);
            }
            # insure it's a positive or negative, integral number of seconds
            elsif ($arg[5] !~ /^\-?\d+$/) {
                privmsg("Try: PUSH <char name> <seconds>", $usernick, 1);
            }
                    elsif (!exists($rps{$arg[4]})) {
                    privmsg("No such username $arg[4].", $usernick, 1);
                    }
                    elsif ($arg[5] > $rps{$arg[4]}{next}) {
                    privmsg("Time to level for $arg[4] ($rps{$arg[4]}{next}s) ".
                        "is lower than $arg[5]; setting TTL to 0.",
                        $usernick, 1);
                        chanmsg("$usernick has pushed $arg[4] $rps{$arg[4]}{next} ".
                            "seconds toward level ".($rps{$arg[4]}{level}+1));
                        $rps{$arg[4]}{next}=0;
                    }
                    else {
                $rps{$arg[4]}{next} -= $arg[5];
                chanmsg("$usernick has pushed $arg[4] $arg[5] seconds ".
                    "toward level ".($rps{$arg[4]}{level}+1).". ".
                    "$arg[4] reaches next level in ".
                    duration($rps{$arg[4]}{next}).".");
            }
                }
                elsif ($arg[3] eq "logout") {
            if (defined($username)) {
                penalize($username,"logout");
            }
            else {
                privmsg("You are not logged in.", $usernick);
            }
        }
                elsif ($arg[3] eq "quest") {
            if (!@{$quest{questers}}) {
                privmsg("There is no active quest.",$usernick);
            }
            elsif ($quest{type} == 1) {
            privmsg(join(", ",(@{$quest{questers}})[0..2]).", and ".
            "$quest{questers}->[3] are on a quest to ".
            "$quest{text}. Quest to complete in ".
            duration($quest{qtime}-time()).".",$usernick);
                    }
                    elsif ($quest{type} == 2) {
                    privmsg(join(", ",(@{$quest{questers}})[0..2]).", and ".
            "$quest{questers}->[3] are on a quest to ".
            "$quest{text}. Participants must first reach ".
            "[$quest{p1}->[0],$quest{p1}->[1]], then ".
            "[$quest{p2}->[0],$quest{p2}->[1]].".
            ($opts{mapurl}?" See $opts{mapurl} to monitor ".
                "their journey's progress.":""),$usernick);
                    }
                }
                elsif ($arg[3] eq "status" && $opts{statuscmd}) {
            if (!defined($username)) {
                privmsg("You are not logged in.", $usernick);
            }
            # argument is optional
            elsif ($arg[4] && !exists($rps{$arg[4]})) {
            privmsg("No such user.",$usernick);
        }
            elsif ($arg[4]) { # optional 'user' argument
            privmsg("$arg[4]: Level $rps{$arg[4]}{level} ".
                "$rps{$arg[4]}{class}; Status: O".
                ($rps{$arg[4]}{online}?"n":"ff")."line; ".
                "TTL: ".duration($rps{$arg[4]}{next})."; ".
                "Idled: ".duration($rps{$arg[4]}{idled}).
                "; Item sum: ".itemsum($arg[4]),$usernick);
        }
        else { # no argument, look up this user
                privmsg("$username: Level $rps{$username}{level} ".
                    "$rps{$username}{class}; Status: O".
                    ($rps{$username}{online}?"n":"ff")."line; ".
                    "TTL: ".duration($rps{$username}{next})."; ".
                    "Idled: ".duration($rps{$username}{idled})."; ".
                    "Item sum: ".itemsum($username),$usernick);
            }
        }
                elsif ($arg[3] eq "whoami") {
            if (!defined($username)) {
                privmsg("You are not logged in.", $usernick);
            }
            else {
                privmsg("You are $username, the level ".
                    $rps{$username}{level}." $rps{$username}{class}. ".
                    "Next level in ".duration($rps{$username}{next}),
                    $usernick);
            }
        }
                elsif ($arg[3] eq "newpass") {
            if (!defined($username)) {
                privmsg("You are not logged in.", $usernick)
                    }
            elsif (!defined($arg[4])) {
            privmsg("Try: NEWPASS <new password>", $usernick);
        }
        else {
                $rps{$username}{pass} = crypt($arg[4],mksalt());
                privmsg("Your password was changed.",$usernick);
            }
        }
                elsif ($arg[3] eq "align") {
            if (!defined($username)) {
                privmsg("You are not logged in.", $usernick)
                    }
            elsif (!defined($arg[4]) || (lc($arg[4]) ne "good" &&
            lc($arg[4]) ne "neutral" && lc($arg[4]) ne "evil")) {
                privmsg("Try: ALIGN <good|neutral|evil>", $usernick);
            }
                    else {
                $rps{$username}{alignment} = substr(lc($arg[4]),0,1);
                chanmsg("$username has changed alignment to: ".lc($arg[4]).
                    ".");
                privmsg("Your alignment was changed to ".lc($arg[4]).".",
                    $usernick);
            }
                }
                elsif ($arg[3] eq "removeme") {
            if (!defined($username)) {
                privmsg("You are not logged in.", $usernick)
                    }
            else {
                privmsg("Account $username removed.",$usernick);
                chanmsg("$arg[0] removed his account, $username, the ".
                    $rps{$username}{class}.".");
                        delete($rps{$username});
                    }
        }
                elsif ($arg[3] eq "help") {
            if (!ha($username)) {
                privmsg("For information on IRPG bot commands, see ".
                    $opts{helpurl}, $usernick);
            }
            else {
                privmsg("Help URL is $opts{helpurl}", $usernick, 1);
                privmsg("Admin commands URL is $opts{admincommurl}",
                    $usernick, 1);
            }
        }
                elsif ($arg[3] eq "die") {
            if (!ha($username)) {
                privmsg("You do not have access to DIE.", $usernick);
            }
            else {
                $opts{reconnect} = 0;
                writedb();
                sts("QUIT :DIE from $arg[0]",1);
            }
        }
                elsif ($arg[3] eq "reloaddb") {
            if (!ha($username)) {
                privmsg("You do not have access to RELOADDB.", $usernick);
            }
            elsif (!$pausemode) {
            privmsg("ERROR: Can only use LOADDB while in PAUSE mode.",
                $usernick, 1);
        }
        else {
                loaddb();
                privmsg("Reread player database file; ".scalar(keys(%rps)).
                " accounts loaded.",$usernick,1);
                    }
        }
                elsif ($arg[3] eq "backup") {
            if (!ha($username)) {
                privmsg("You do not have access to BACKUP.", $usernick);
            }
            else {
                backup();
                privmsg("$opts{dbfile} copied to ".
                    ".dbbackup/$opts{dbfile}".time(),$usernick,1);
            }
        }
                elsif ($arg[3] eq "pause") {
            if (!ha($username)) {
                privmsg("You do not have access to PAUSE.", $usernick);
            }
            else {
                $pausemode = $pausemode ? 0 : 1;
                privmsg("PAUSE_MODE set to $pausemode.",$usernick,1);
            }
        }
                elsif ($arg[3] eq "silent") {
            if (!ha($username)) {
                privmsg("You do not have access to SILENT.", $usernick);
            }
            elsif (!defined($arg[4]) || $arg[4] < 0 || $arg[4] > 3) {
            privmsg("Try: SILENT <mode>", $usernick,1);
        }
        else {
                $silentmode = $arg[4];
                privmsg("SILENT_MODE set to $silentmode.",$usernick,1);
            }
        }
                elsif ($arg[3] eq "jump") {
            if (!ha($username)) {
                privmsg("You do not have access to JUMP.", $usernick);
            }
            elsif (!defined($arg[4])) {
            privmsg("Try: JUMP <server[:port]>", $usernick, 1);
        }
        else {
                writedb();
                sts("QUIT :JUMP to $arg[4] from $arg[0]");
                unshift(@{$opts{servers}},$arg[4]);
                        close($sock);
                        sleep(3);
                        goto CONNECT;
                    }
        }
                elsif ($arg[3] eq "restart") {
            if (!ha($username)) {
                privmsg("You do not have access to RESTART.", $usernick);
            }
            else {
                writedb();
                sts("QUIT :RESTART from $arg[0]",1);
                close($sock);
                exec("perl $0");
            }
        }
                elsif ($arg[3] eq "clearq") {
            if (!ha($username)) {
                privmsg("You do not have access to CLEARQ.", $usernick);
            }
            else {
                undef(@queue);
                chanmsg("Outgoing message queue cleared by $arg[0].");
                privmsg("Outgoing message queue cleared.",$usernick,1);
            }
        }
                elsif ($arg[3] eq "info") {
            my $info;
                    if (!ha($username) && $opts{allowuserinfo}) {
                        $info = "IRPG bot v$version by jotun, ".
                            "http://idlerpg.net/. On via server: ".
                            $opts{servers}->[0].". Admins online: ".
                            join(", ", map { $rps{$_}{nick} }
                                          grep { $rps{$_}{isadmin} &&
                        $rps{$_}{online} } keys(%rps)).".";
                        privmsg($info, $usernick);
                    }
                    elsif (!ha($username) && !$opts{allowuserinfo}) {
                    privmsg("You do not have access to INFO.", $usernick);
                    }
                    else {
                my $queuedbytes = 0;
                        $queuedbytes += (length($_)+2) for @queue; # +2 = \r\n
                                                           $info = sprintf(
                                                               "%.2fkb sent, %.2fkb received in %s. %d IRPG users ".
                                                               "online of %d total users. %d accounts created since ".
                                                               "startup. PAUSE_MODE is %d, SILENT_MODE is %d. ".
                                                               "Outgoing queue is %d bytes in %d items. On via: %s. ".
                                                               "Admins online: %s.",
                                                               $outbytes/1024,
                                                               $inbytes/1024,
                                                               duration(time()-$^T),
                                                               scalar(grep { $rps{$_}{online} } keys(%rps)),
                            scalar(keys(%rps)),
                            $registrations,
                            $pausemode,
                            $silentmode,
                            $queuedbytes,
                            scalar(@queue),
                            $opts{servers}->[0],
                            join(", ",map { $rps{$_}{nick} }
                                      grep { $rps{$_}{isadmin} && $rps{$_}{online} }
                                      keys(%rps)));
                        privmsg($info, $usernick, 1);
                    }
                }
                elsif ($arg[3] eq "login") {
            if (defined($username)) {
                notice("Sorry, you are already online as $username.",
                    $usernick);
            }
            else {
                if ($#arg < 5 || $arg[5] eq "") {
                            notice("Try: LOGIN <username> <password>", $usernick);
            }
            elsif (!exists $rps{$arg[4]}) {
                notice("Sorry, no such account name. Note that ".
                    "account names are case sensitive.",$usernick);
            }
                        elsif (!exists $onchan{$usernick}) {
                notice("Sorry, you're not in $opts{botchan}.",
                    $usernick);
            }
                        elsif ($rps{$arg[4]}{pass} ne
                               crypt($arg[5],$rps{$arg[4]}{pass})) {
                notice("Wrong password.", $usernick);
            }
                        else {
                if ($opts{voiceonlogin}) {
                    sts("MODE $opts{botchan} +v :$usernick");
                }
                $rps{$arg[4]}{online} = 1;
                $rps{$arg[4]}{nick} = $usernick;
                $rps{$arg[4]}{userhost} = $arg[0];
                $rps{$arg[4]}{lastlogin} = time();
                chanmsg("$arg[4], the level $rps{$arg[4]}{level} ".
                    "$rps{$arg[4]}{class}, is now online from ".
                    "nickname $usernick. Next level in ".
                    duration($rps{$arg[4]}{next}).".");
                notice("Logon successful. Next level in ".
                    duration($rps{$arg[4]}{next}).".", $usernick);
            }
                    }
                }
            }
            # penalize returns true if user was online and successfully penalized.
            # if the user is not logged in, then penalize() fails. so, if user is
            # offline, and they say something including "http:", and they've been on
            # the channel less than 90 seconds, and the http:-style ban is on, then
            # check to see if their url is in @{$opts{okurl}}. if not, kickban them
            elsif (!penalize($username,"privmsg",length("@arg[3..$#arg]")) &&
                index(lc("@arg[3..$#arg]"),"http:") != -1 &&
                (time()-$onchan{$usernick}) < 90 && $opts{doban}) {
            my $isokurl = 0;
                for (@{$opts{okurl}}) {
        if (index(lc("@arg[3..$#arg]"),lc($_)) != -1) { $isokurl = 1; }
    }
                if (!$isokurl) {
                    sts("MODE $opts{botchan} +b $arg[0]");
                    sts("KICK $opts{botchan} $usernick :No advertising; ban will ".
                        "be lifted within the hour.");
                    push(@bans,$arg[0]) if @bans < 12;
                }
            }
        }

    public function duration()
    { # return human duration of seconds
        my $s = shift;
        return "NA ($s)" if $s !~ /^\d+$/;
        return sprintf("%d day%s, %02d:%02d:%02d",$s/86400,int($s/86400)==1?"":"s",
            ($s%86400)/3600,($s%3600)/60,($s%60));
    }

    public function ts()
    { # timestamp
        my @ts = localtime(time());
        return sprintf("[%02d/%02d/%02d %02d:%02d:%02d] ",
            $ts[4]+1,$ts[3],$ts[5]%100,$ts[2],$ts[1],$ts[0]);
    }

    public function hog()
    { # summon the hand of god
        my @players = grep { $rps{$_}{online} } keys(%rps);
        my $player = $players[rand(@players)];
        my $win = int(rand(5));
        my $time = int(((5 + int(rand(71)))/100) * $rps{$player}{next});
        if ($win) {
            chanmsg(clog("Verily I say unto thee, the Heavens have burst forth, ".
                "and the blessed hand of God carried $player ".
                duration($time)." toward level ".($rps{$player}{level}+1).
                "."));
            $rps{$player}{next} -= $time;
        }
        else {
            chanmsg(clog("Thereupon He stretched out His little finger among them ".
                "and consumed $player with fire, slowing the heathen ".
                duration($time)." from level ".($rps{$player}{level}+1).
                "."));
            $rps{$player}{next} += $time;
        }
        chanmsg("$player reaches next level in ".duration($rps{$player}{next}).".");
    }

    public function rpcheck()
    { # check levels, update database
        # check splits hash to see if any split users have expired
        checksplits() if $opts{detectsplits};
        # send out $freemessages lines of text from the outgoing message queue
        fq();
        # clear registration limiting
        $lastreg = 0;
        my $online = scalar(grep { $rps{$_}{online} } keys(%rps));
        # there's really nothing to do here if there are no online users
        return unless $online;
        my $onlineevil = scalar(grep { $rps{$_}{online} &&
        $rps{$_}{alignment} eq "e" } keys(%rps));
        my $onlinegood = scalar(grep { $rps{$_}{online} &&
        $rps{$_}{alignment} eq "g" } keys(%rps));
        if (!$opts{noscale}) {
            if (rand((20*86400)/$opts{self_clock}) < $online) { hog(); }
            if (rand((24*86400)/$opts{self_clock}) < $online) { team_battle(); }
            if (rand((8*86400)/$opts{self_clock}) < $online) { calamity(); }
            if (rand((4*86400)/$opts{self_clock}) < $online) { godsend(); }
        }
        else {
            hog() if rand(4000) < 1;
            team_battle() if rand(4000) < 1;
            calamity() if rand(4000) < 1;
            godsend() if rand(2000) < 1;
        }
        if (rand((8*86400)/$opts{self_clock}) < $onlineevil) { evilness(); }
        if (rand((12*86400)/$opts{self_clock}) < $onlinegood) { goodness(); }

        moveplayers();

        # statements using $rpreport do not bother with scaling by the clock because
        # $rpreport is adjusted by the number of seconds since last rpcheck()
        if ($rpreport%120==0 && $opts{writequestfile}) { writequestfile(); }
        if (time() > $quest{qtime}) {
            if (!@{$quest{questers}}) { quest(); }
            elsif ($quest{type} == 1) {
            chanmsg(clog(join(", ",(@{$quest{questers}})[0..2]).", and ".
            "$quest{questers}->[3] have blessed the realm by ".
            "completing their quest! 25% of their burden is ".
            "eliminated."));
                for (@{$quest{questers}}) {
                $rps{$_}{next} = int($rps{$_}{next} * .75);
            }
                undef(@{$quest{questers}});
                $quest{qtime} = time() + 21600;
            }
            # quest type 2 awards are handled in moveplayers()
        }
        if ($rpreport && $rpreport%36000==0) { # 10 hours
            my @u = sort { $rps{$b}{level} <=> $rps{$a}{level} ||
            $rps{$a}{next}  <=> $rps{$b}{next} } keys(%rps);
            chanmsg("Idle RPG Top Players:") if @u;
            for my $i (0..2) {
                $#u >= $i and
                chanmsg("$u[$i], the level $rps{$u[$i]}{level} ".
                    "$rps{$u[$i]}{class}, is #" . ($i + 1) . "! Next level in ".
                    (duration($rps{$u[$i]}{next})).".");
            }
            backup();
        }
        if ($rpreport%3600==0 && $rpreport) { # 1 hour
            my @players = grep { $rps{$_}{online} &&
            $rps{$_}{level} > 44 } keys(%rps);
            # 20% of all players must be level 45+
            if ((scalar(@players)/scalar(grep { $rps{$_}{online} } keys(%rps))) > .15) {
                challenge_opp($players[int(rand(@players))]);
            }
            while (@bans) {
                sts("MODE $opts{botchan} -bbbb :@bans[0..3]");
                splice(@bans,0,4);
            }
        }
        if ($rpreport%1800==0) { # 30 mins
            if ($opts{botnick} ne $primnick) {
                sts($opts{botghostcmd}) if $opts{botghostcmd};
                sts("NICK $primnick");
            }
        }
        if ($rpreport%600==0 && $pausemode) { # warn every 10m
            chanmsg("WARNING: Cannot write database in PAUSE mode!");
        }
        # do not write in pause mode, and do not write if not yet connected. (would
        # log everyone out if the bot failed to connect. $lasttime = time() on
        # successful join to $opts{botchan}, initial value is 1). if fails to open
        # $opts{dbfile}, will not update $lasttime and so should have correct values
        # on next rpcheck().
        if ($lasttime != 1) {
            my $curtime=time();
            for my $k (keys(%rps)) {
                if ($rps{$k}{online} && exists $rps{$k}{nick} &&
                $rps{$k}{nick} && exists $onchan{$rps{$k}{nick}}) {
                    $rps{$k}{next} -= ($curtime - $lasttime);
                    $rps{$k}{idled} += ($curtime - $lasttime);
                    if ($rps{$k}{next} < 1) {
                        $rps{$k}{level}++;
                        if ($rps{$k}{level} > 60) {
                            $rps{$k}{next} = int(($opts{rpbase} *
                                    ($opts{rpstep}**60)) +
                                (86400*($rps{$k}{level} - 60)));
                        }
                        else {
                            $rps{$k}{next} = int($opts{rpbase} *
                                ($opts{rpstep}**$rps{$k}{level}));
                        }
                        chanmsg("$k, the $rps{$k}{class}, has attained level ".
                            "$rps{$k}{level}! Next level in ".
                            duration($rps{$k}{next}).".");
                        find_item($k);
                        challenge_opp($k);
                    }
                }
                # attempt to make sure this is an actual user, and not just an
                # artifact of a bad PEVAL
            }
            if (!$pausemode && $rpreport%60==0) { writedb(); }
            $rpreport += $opts{self_clock};
            $lasttime = $curtime;
        }
    }

    public function challenge_opp { # pit argument player against random player
        my $u = shift;
        if ($rps{$u}{level} < 25) { return unless rand(4) < 1; }
        my @opps = grep { $rps{$_}{online} && $u ne $_ } keys(%rps);
        return unless @opps;
        my $opp = $opps[int(rand(@opps))];
        $opp = $primnick if rand(@opps+1) < 1;
        my $mysum = itemsum($u,1);
        my $oppsum = itemsum($opp,1);
        my $myroll = int(rand($mysum));
        my $opproll = int(rand($oppsum));
        if ($myroll >= $opproll) {
            my $gain = ($opp eq $primnick)?20:int($rps{$opp}{level}/4);
            $gain = 7 if $gain < 7;
            $gain = int(($gain/100)*$rps{$u}{next});
            chanmsg(clog("$u [$myroll/$mysum] has challenged $opp [$opproll/".
                "$oppsum] in combat and won! ".duration($gain)." is ".
                "removed from $u\'s clock."));
            $rps{$u}{next} -= $gain;
            chanmsg("$u reaches next level in ".duration($rps{$u}{next}).".");
            my $csfactor = $rps{$u}{alignment} eq "g" ? 50 :
                $rps{$u}{alignment} eq "e" ? 20 :
                35;
            if (rand($csfactor) < 1 && $opp ne $primnick) {
                $gain = int(((5 + int(rand(20)))/100) * $rps{$opp}{next});
                chanmsg(clog("$u has dealt $opp a Critical Strike! ".
                    duration($gain)." is added to $opp\'s clock."));
                $rps{$opp}{next} += $gain;
                chanmsg("$opp reaches next level in ".duration($rps{$opp}{next}).
                    ".");
            }
            elsif (rand(25) < 1 && $opp ne $primnick && $rps{$u}{level} > 19) {
                my @items = ("ring","amulet","charm","weapon","helm","tunic",
                             "pair of gloves","set of leggings","shield",
                             "pair of boots");
                my $type = $items[rand(@items)];
                if (int($rps{$opp}{item}{$type}) > int($rps{$u}{item}{$type})) {
                    chanmsg(clog("In the fierce battle, $opp dropped his level ".
                        int($rps{$opp}{item}{$type})." $type! $u picks ".
                        "it up, tossing his old level ".
                        int($rps{$u}{item}{$type})." $type to $opp."));
                    my $tempitem = $rps{$u}{item}{$type};
                    $rps{$u}{item}{$type}=$rps{$opp}{item}{$type};
                    $rps{$opp}{item}{$type} = $tempitem;
                }
            }
        }
        else {
            my $gain = ($opp eq $primnick)?10:int($rps{$opp}{level}/7);
            $gain = 7 if $gain < 7;
            $gain = int(($gain/100)*$rps{$u}{next});
            chanmsg(clog("$u [$myroll/$mysum] has challenged $opp [$opproll/".
                "$oppsum] in combat and lost! ".duration($gain)." is ".
                "added to $u\'s clock."));
            $rps{$u}{next} += $gain;
            chanmsg("$u reaches next level in ".duration($rps{$u}{next}).".");
        }
    }

    public function team_battle { # pit three players against three other players
        my @opp = grep { $rps{$_}{online} } keys(%rps);
        return if @opp < 6;
        splice(@opp,int(rand(@opp)),1) while @opp > 6;
        fisher_yates_shuffle(\@opp);
        my $mysum = itemsum($opp[0],1) + itemsum($opp[1],1) + itemsum($opp[2],1);
        my $oppsum = itemsum($opp[3],1) + itemsum($opp[4],1) + itemsum($opp[5],1);
        my $gain = $rps{$opp[0]}{next};
        for my $p (1,2) {
        $gain = $rps{$opp[$p]}{next} if $gain > $rps{$opp[$p]}{next};
        }
        $gain = int($gain*.20);
        my $myroll = int(rand($mysum));
        my $opproll = int(rand($oppsum));
        if ($myroll >= $opproll) {
            chanmsg(clog("$opp[0], $opp[1], and $opp[2] [$myroll/$mysum] have ".
                "team battled $opp[3], $opp[4], and $opp[5] [$opproll/".
                "$oppsum] and won! ".duration($gain)." is removed from ".
                "their clocks."));
            $rps{$opp[0]}{next} -= $gain;
            $rps{$opp[1]}{next} -= $gain;
            $rps{$opp[2]}{next} -= $gain;
        }
        else {
            chanmsg(clog("$opp[0], $opp[1], and $opp[2] [$myroll/$mysum] have ".
                "team battled $opp[3], $opp[4], and $opp[5] [$opproll/".
                "$oppsum] and lost! ".duration($gain)." is added to ".
                "their clocks."));
            $rps{$opp[0]}{next} += $gain;
            $rps{$opp[1]}{next} += $gain;
            $rps{$opp[2]}{next} += $gain;
        }
    }

    public function find_item { # find item for argument player
        my $u = shift;
        my @items = ("ring","amulet","charm","weapon","helm","tunic",
                     "pair of gloves","set of leggings","shield","pair of boots");
        my $type = $items[rand(@items)];
        my $level = 1;
        my $ulevel;
        for my $num (1 .. int($rps{$u}{level}*1.5)) {
            if (rand(1.4**($num/4)) < 1) {
                $level = $num;
            }
        }
        if ($rps{$u}{level} >= 25 && rand(40) < 1) {
            $ulevel = 50+int(rand(25));
            if ($ulevel >= $level && $ulevel > int($rps{$u}{item}{helm})) {
                notice("The light of the gods shines down upon you! You have ".
                    "found the level $ulevel Mattt's Omniscience Grand Crown! ".
                    "Your enemies fall before you as you anticipate their ".
                    "every move.",$rps{$u}{nick});
                $rps{$u}{item}{helm} = $ulevel."a";
                return;
            }
        }
        elsif ($rps{$u}{level} >= 25 && rand(40) < 1) {
        $ulevel = 50+int(rand(25));
            if ($ulevel >= $level && $ulevel > int($rps{$u}{item}{ring})) {
                notice("The light of the gods shines down upon you! You have ".
                    "found the level $ulevel Juliet's Glorious Ring of ".
                    "Sparkliness! You enemies are blinded by both its glory ".
                    "and their greed as you bring desolation upon them.",
                    $rps{$u}{nick});
                $rps{$u}{item}{ring} = $ulevel."h";
                return;
            }
        }
        elsif ($rps{$u}{level} >= 30 && rand(40) < 1) {
        $ulevel = 75+int(rand(25));
            if ($ulevel >= $level && $ulevel > int($rps{$u}{item}{tunic})) {
                notice("The light of the gods shines down upon you! You have ".
                    "found the level $ulevel Res0's Protectorate Plate Mail! ".
                    "Your enemies cower in fear as their attacks have no ".
                    "effect on you.",$rps{$u}{nick});
                $rps{$u}{item}{tunic} = $ulevel."b";
                return;
            }
        }
        elsif ($rps{$u}{level} >= 35 && rand(40) < 1) {
        $ulevel = 100+int(rand(25));
            if ($ulevel >= $level && $ulevel > int($rps{$u}{item}{amulet})) {
                notice("The light of the gods shines down upon you! You have ".
                    "found the level $ulevel Dwyn's Storm Magic Amulet! Your ".
                    "enemies are swept away by an elemental fury before the ".
                    "war has even begun",$rps{$u}{nick});
                $rps{$u}{item}{amulet} = $ulevel."c";
                return;
            }
        }
        elsif ($rps{$u}{level} >= 40 && rand(40) < 1) {
        $ulevel = 150+int(rand(25));
            if ($ulevel >= $level && $ulevel > int($rps{$u}{item}{weapon})) {
                notice("The light of the gods shines down upon you! You have ".
                    "found the level $ulevel Jotun's Fury Colossal Sword! Your ".
                    "enemies' hatred is brought to a quick end as you arc your ".
                    "wrist, dealing the crushing blow.",$rps{$u}{nick});
                $rps{$u}{item}{weapon} = $ulevel."d";
                return;
            }
        }
        elsif ($rps{$u}{level} >= 45 && rand(40) < 1) {
        $ulevel = 175+int(rand(26));
            if ($ulevel >= $level && $ulevel > int($rps{$u}{item}{weapon})) {
                notice("The light of the gods shines down upon you! You have ".
                    "found the level $ulevel Drdink's Cane of Blind Rage! Your ".
                    "enemies are tossed aside as you blindly swing your arm ".
                    "around hitting stuff.",$rps{$u}{nick});
                $rps{$u}{item}{weapon} = $ulevel."e";
                return;
            }
        }
        elsif ($rps{$u}{level} >= 48 && rand(40) < 1) {
        $ulevel = 250+int(rand(51));
            if ($ulevel >= $level && $ulevel >
                int($rps{$u}{item}{"pair of boots"})) {
                notice("The light of the gods shines down upon you! You have ".
                    "found the level $ulevel Mrquick's Magical Boots of ".
                    "Swiftness! Your enemies are left choking on your dust as ".
                    "you run from them very, very quickly.",$rps{$u}{nick});
                $rps{$u}{item}{"pair of boots"} = $ulevel."f";
                return;
            }
        }
        elsif ($rps{$u}{level} >= 52 && rand(40) < 1) {
        $ulevel = 300+int(rand(51));
            if ($ulevel >= $level && $ulevel > int($rps{$u}{item}{weapon})) {
                notice("The light of the gods shines down upon you! You have ".
                    "found the level $ulevel Jeff's Cluehammer of Doom! Your ".
                    "enemies are left with a sudden and intense clarity of ".
                    "mind... even as you relieve them of it.",$rps{$u}{nick});
                $rps{$u}{item}{weapon} = $ulevel."g";
                return;
            }
        }
        if ($level > int($rps{$u}{item}{$type})) {
            notice("You found a level $level $type! Your current $type is only ".
                "level ".int($rps{$u}{item}{$type}).", so it seems Luck is ".
                "with you!",$rps{$u}{nick});
            $rps{$u}{item}{$type} = $level;
        }
        else {
            notice("You found a level $level $type. Your current $type is level ".
                int($rps{$u}{item}{$type}).", so it seems Luck is against you. ".
                "You toss the $type.",$rps{$u}{nick});
        }
    }

    public function loaddb { # load the players database
        backup();
        my $l;
        %rps = ();
        if (!open(RPS,$opts{dbfile}) && -e $opts{dbfile}) {
            sts("QUIT :loaddb() failed: $!");
        }
        while ($l=<RPS>) {
            chomp($l);
            next if $l =~ /^#/; # skip comments
            my @i = split("\t",$l);
            print Dumper(@i) if @i != 32;
            if (@i != 32) {
                sts("QUIT: Anomaly in loaddb(); line $. of $opts{dbfile} has ".
                    "wrong fields (".scalar(@i).")");
                debug("Anomaly in loaddb(); line $. of $opts{dbfile} has wrong ".
                    "fields (".scalar(@i).")",1);
            }
            if (!$sock) { # if not RELOADDB
                if ($i[8]) { $prev_online{$i[7]}=$i[0]; } # log back in
            }
            ($rps{$i[0]}{pass},
            $rps{$i[0]}{isadmin},
            $rps{$i[0]}{level},
            $rps{$i[0]}{class},
            $rps{$i[0]}{next},
            $rps{$i[0]}{nick},
            $rps{$i[0]}{userhost},
            $rps{$i[0]}{online},
            $rps{$i[0]}{idled},
            $rps{$i[0]}{x},
            $rps{$i[0]}{y},
            $rps{$i[0]}{pen_mesg},
            $rps{$i[0]}{pen_nick},
            $rps{$i[0]}{pen_part},
            $rps{$i[0]}{pen_kick},
            $rps{$i[0]}{pen_quit},
            $rps{$i[0]}{pen_quest},
            $rps{$i[0]}{pen_logout},
            $rps{$i[0]}{created},
            $rps{$i[0]}{lastlogin},
            $rps{$i[0]}{item}{amulet},
            $rps{$i[0]}{item}{charm},
            $rps{$i[0]}{item}{helm},
            $rps{$i[0]}{item}{"pair of boots"},
            $rps{$i[0]}{item}{"pair of gloves"},
            $rps{$i[0]}{item}{ring},
            $rps{$i[0]}{item}{"set of leggings"},
            $rps{$i[0]}{item}{shield},
            $rps{$i[0]}{item}{tunic},
            $rps{$i[0]}{item}{weapon},
            $rps{$i[0]}{alignment}) = (@i[1..7],($sock?$i[8]:0),@i[9..$#i]);
        }
        close(RPS);
        debug("loaddb(): loaded ".scalar(keys(%rps))." accounts, ".
        scalar(keys(%prev_online))." previously online.");
    }

    public function moveplayers {
        return unless $lasttime > 1;
        my $onlinecount = grep { $rps{$_}{online} } keys %rps;
        return unless $onlinecount;
        for (my $i=0;$i<$opts{self_clock};++$i) {
            # temporary hash to hold player positions, detect collisions
            my %positions = ();
            if ($quest{type} == 2 && @{$quest{questers}}) {
                my $allgo = 1; # have all users reached <p1|p2>?
                for (@{$quest{questers}}) {
                    if ($quest{stage}==1) {
                        if ($rps{$_}{x} != $quest{p1}->[0] ||
                            $rps{$_}{y} != $quest{p1}->[1]) {
                            $allgo=0;
                            last();
                        }
                    }
                    else {
                        if ($rps{$_}{x} != $quest{p2}->[0] ||
                            $rps{$_}{y} != $quest{p2}->[1]) {
                            $allgo=0;
                            last();
                        }
                    }
                }
                # all participants have reached point 1, now point 2
                if ($quest{stage}==1 && $allgo) {
                    $quest{stage}=2;
                    $allgo=0; # have not all reached p2 yet
                }
                elsif ($quest{stage} == 2 && $allgo) {
                chanmsg(clog(join(", ",(@{$quest{questers}})[0..2]).", ".
                "and $quest{questers}->[3] have completed their ".
                "journey! 25% of their burden is eliminated."));
                    for (@{$quest{questers}}) {
                    $rps{$_}{next} = int($rps{$_}{next} * .75);
                }
                    undef(@{$quest{questers}});
                    $quest{qtime} = time() + 21600; # next quest starts in 6 hours
                    $quest{type} = 1; # probably not needed
                    writequestfile();
                }
                else {
                    my(%temp,$player);
                    # load keys of %temp with online users
                    ++@temp{grep { $rps{$_}{online} } keys(%rps)};
                    # delete questers from list
                    delete(@temp{@{$quest{questers}}});
                    while ($player = each(%temp)) {
                        $rps{$player}{x} += int(rand(3))-1;
                        $rps{$player}{y} += int(rand(3))-1;
                        # if player goes over edge, wrap them back around
                        if ($rps{$player}{x} > $opts{mapx}) { $rps{$player}{x}=0; }
                        if ($rps{$player}{y} > $opts{mapy}) { $rps{$player}{y}=0; }
                        if ($rps{$player}{x} < 0) { $rps{$player}{x}=$opts{mapx}; }
                        if ($rps{$player}{y} < 0) { $rps{$player}{y}=$opts{mapy}; }

                        if (exists($positions{$rps{$player}{x}}{$rps{$player}{y}}) &&
                            !$positions{$rps{$player}{x}}{$rps{$player}{y}}{battled}) {
                            if ($rps{$positions{$rps{$player}{x}}{$rps{$player}{y}}{user}}{isadmin} &&
                                !$rps{$player}{isadmin} && rand(100) < 1) {
                                chanmsg("$player encounters ".
                                    $positions{$rps{$player}{x}}{$rps{$player}{y}}{user}.
                                    " and bows humbly.");
                            }
                            if (rand($onlinecount) < 1) {
                                $positions{$rps{$player}{x}}{$rps{$player}{y}}{battled}=1;
                                collision_fight($player,
                                    $positions{$rps{$player}{x}}{$rps{$player}{y}}{user});
                            }
                        }
                        else {
                            $positions{$rps{$player}{x}}{$rps{$player}{y}}{battled}=0;
                            $positions{$rps{$player}{x}}{$rps{$player}{y}}{user}=$player;
                        }
                    }
                    for (@{$quest{questers}}) {
                        if ($quest{stage} == 1) {
                            if (rand(100) < 1) {
                                if ($rps{$_}{x} != $quest{p1}->[0]) {
                                    $rps{$_}{x} += ($rps{$_}{x} < $quest{p1}->[0] ?
                                        1 : -1);
                                }
                                if ($rps{$_}{y} != $quest{p1}->[1]) {
                                    $rps{$_}{y} += ($rps{$_}{y} < $quest{p1}->[1] ?
                                        1 : -1);
                                }
                            }
                        }
                        elsif ($quest{stage}==2) {
                            if (rand(100) < 1) {
                                if ($rps{$_}{x} != $quest{p2}->[0]) {
                                    $rps{$_}{x} += ($rps{$_}{x} < $quest{p2}->[0] ?
                                        1 : -1);
                                }
                                if ($rps{$_}{y} != $quest{p2}->[1]) {
                                    $rps{$_}{y} += ($rps{$_}{y} < $quest{p2}->[1] ?
                                        1 : -1);
                                }
                            }
                        }
                    }
                }
            }
        else {
                for my $player (keys(%rps)) {
                    next unless $rps{$player}{online};
                    $rps{$player}{x} += int(rand(3))-1;
                    $rps{$player}{y} += int(rand(3))-1;
                    # if player goes over edge, wrap them back around
                    if ($rps{$player}{x} > $opts{mapx}) { $rps{$player}{x} = 0; }
                    if ($rps{$player}{y} > $opts{mapy}) { $rps{$player}{y} = 0; }
                    if ($rps{$player}{x} < 0) { $rps{$player}{x} = $opts{mapx}; }
                    if ($rps{$player}{y} < 0) { $rps{$player}{y} = $opts{mapy}; }
                    if (exists($positions{$rps{$player}{x}}{$rps{$player}{y}}) &&
                        !$positions{$rps{$player}{x}}{$rps{$player}{y}}{battled}) {
                        if ($rps{$positions{$rps{$player}{x}}{$rps{$player}{y}}{user}}{isadmin} &&
                            !$rps{$player}{isadmin} && rand(100) < 1) {
                            chanmsg("$player encounters ".
                                $positions{$rps{$player}{x}}{$rps{$player}{y}}{user}.
                                " and bows humbly.");
                        }
                        if (rand($onlinecount) < 1) {
                            $positions{$rps{$player}{x}}{$rps{$player}{y}}{battled}=1;
                            collision_fight($player,
                                $positions{$rps{$player}{x}}{$rps{$player}{y}}{user});
                        }
                    }
                    else {
                        $positions{$rps{$player}{x}}{$rps{$player}{y}}{battled}=0;
                        $positions{$rps{$player}{x}}{$rps{$player}{y}}{user}=$player;
                    }
                }
            }
        }
    }

    private function help()
    { # print help message
        (my $prog = $0) =~ s/^.*\///;

        print "
    usage: $prog [OPTIONS]
      --help, -h           Print this message
      --verbose, -v        Print verbose messages
      --server, -s         Specify IRC server:port to connect to
      --botnick, -n        Bot's IRC nick
      --botuser, -u        Bot's username
      --botrlnm, -r        Bot's real name
      --botchan, -c        IRC channel to join
      --botident, -p       Specify identify-to-services command
      --botmodes, -m       Specify usermodes for the bot to set upon connect
      --botopcmd, -o       Specify command to send to server on successful connect
      --botghostcmd, -g    Specify command to send to server to regain primary
                           nickname when in use
      --doban              Advertisement ban on/off flag
      --okurl, -k          Bot will not ban for web addresses that contain these
                           strings
      --debug              Debug on/off flag
      --helpurl            URL to refer new users to
      --admincommurl       URL to refer admins to

      Timing parameters:
      --rpbase             Base time to level up
      --rpstep             Time to next level = rpbase * (rpstep ** CURRENT_LEVEL)
      --rppenstep          PENALTY_SECS=(PENALTY*(RPPENSTEP**CURRENT_LEVEL))

    ";
    }

    public function itemsum()
    {
        my $user = shift;
        # is this for a battle? if so, good users get a 10% boost and evil users get
        # a 10% detriment
        my $battle = shift;
        return -1 unless defined $user;
        my $sum = 0;
        if ($user eq $primnick) {
            for my $u (keys(%rps)) {
                $sum = itemsum($u) if $sum < itemsum($u);
            }
            return $sum+1;
        }
        if (!exists($rps{$user})) { return -1; }
        $sum += int($rps{$user}{item}{$_}) for keys(%{$rps{$user}{item}});
        if ($battle) {
            return $rps{$user}{alignment} eq 'e' ? int($sum*.9) :
                $rps{$user}{alignment} eq 'g' ? int($sum*1.1) :
                $sum;
        }
        return $sum;
    }

    public function calamity()
{ # suffer a little one
        my @players = grep { $rps{$_}{online} } keys(%rps);
        return unless @players;
        my $player = $players[rand(@players)];
        if (rand(10) < 1) {
            my @items = ("amulet","charm","weapon","tunic","set of leggings",
                         "shield");
            my $type = $items[rand(@items)];
            if ($type eq "amulet") {
                chanmsg(clog("$player fell, chipping the stone in his amulet! ".
                    "$player\'s $type loses 10% of its effectiveness."));
            }
            elsif ($type eq "charm") {
                chanmsg(clog("$player slipped and dropped his charm in a dirty ".
                    "bog! $player\'s $type loses 10% of its ".
                    "effectiveness."));
            }
            elsif ($type eq "weapon") {
                chanmsg(clog("$player left his weapon out in the rain to rust! ".
                    "$player\'s $type loses 10% of its effectiveness."));
            }
            elsif ($type eq "tunic") {
                chanmsg(clog("$player spilled a level 7 shrinking potion on his ".
                    "tunic! $player\'s $type loses 10% of its ".
                    "effectiveness."));
            }
            elsif ($type eq "shield") {
                chanmsg(clog("$player\'s shield was damaged by a dragon's fiery ".
                    "breath! $player\'s $type loses 10% of its ".
                    "effectiveness."));
            }
            else {
                chanmsg(clog("$player burned a hole through his leggings while ".
                    "ironing them! $player\'s $type loses 10% of its ".
                    "effectiveness."));
            }
            my $suffix="";
            if ($rps{$player}{item}{$type} =~ /(\D)$/) { $suffix=$1; }
            $rps{$player}{item}{$type} = int(int($rps{$player}{item}{$type}) * .9);
            $rps{$player}{item}{$type}.=$suffix;
        }
        else {
            my $time = int(int(5 + rand(8)) / 100 * $rps{$player}{next});
            if (!open(Q,$opts{eventsfile})) {
                return chanmsg("ERROR: Failed to open $opts{eventsfile}: $!");
            }
            my($i,$actioned);
            while (my $line = <Q>) {
                chomp($line);
                if ($line =~ /^C (.*)/ && rand(++$i) < 1) { $actioned = $1; }
            }
            chanmsg(clog("$player $actioned. This terrible calamity has slowed ".
                "them ".duration($time)." from level ".
                ($rps{$player}{level}+1)."."));
            $rps{$player}{next} += $time;
            chanmsg("$player reaches next level in ".duration($rps{$player}{next}).
                ".");
        }
    }

    public function godsend { # bless the unworthy
        my @players = grep { $rps{$_}{online} } keys(%rps);
        return unless @players;
        my $player = $players[rand(@players)];
        if (rand(10) < 1) {
            my @items = ("amulet","charm","weapon","tunic","set of leggings",
                         "shield");
            my $type = $items[rand(@items)];
            if ($type eq "amulet") {
                chanmsg(clog("$player\'s amulet was blessed by a passing cleric! ".
                    "$player\'s $type gains 10% effectiveness."));
            }
            elsif ($type eq "charm") {
                chanmsg(clog("$player\'s charm ate a bolt of lightning! ".
                    "$player\'s $type gains 10% effectiveness."));
            }
            elsif ($type eq "weapon") {
                chanmsg(clog("$player sharpened the edge of his weapon! ".
                    "$player\'s $type gains 10% effectiveness."));
            }
            elsif ($type eq "tunic") {
                chanmsg(clog("A magician cast a spell of Rigidity on $player\'s ".
                    "tunic! $player\'s $type gains 10% effectiveness."));
            }
            elsif ($type eq "shield") {
                chanmsg(clog("$player reinforced his shield with a dragon's ".
                    "scales! $player\'s $type gains 10% effectiveness."));
            }
            else {
                chanmsg(clog("The local wizard imbued $player\'s pants with a ".
                    "Spirit of Fortitude! $player\'s $type gains 10% ".
                    "effectiveness."));
            }
            my $suffix="";
            if ($rps{$player}{item}{$type} =~ /(\D)$/) { $suffix=$1; }
            $rps{$player}{item}{$type} = int(int($rps{$player}{item}{$type}) * 1.1);
            $rps{$player}{item}{$type}.=$suffix;
        }
        else {
            my $time = int(int(5 + rand(8)) / 100 * $rps{$player}{next});
            my $actioned;
            if (!open(Q,$opts{eventsfile})) {
                return chanmsg("ERROR: Failed to open $opts{eventsfile}: $!");
            }
            my $i;
            while (my $line = <Q>) {
                chomp($line);
                if ($line =~ /^G (.*)/ && rand(++$i) < 1) {
                    $actioned = $1;
                }
            }
            chanmsg(clog("$player $actioned! This wondrous godsend has ".
                "accelerated them ".duration($time)." towards level ".
                ($rps{$player}{level}+1)."."));
            $rps{$player}{next} -= $time;
            chanmsg("$player reaches next level in ".duration($rps{$player}{next}).
                ".");
        }
    }

    public function quest {
        @{$quest{questers}} = grep { $rps{$_}{online} && $rps{$_}{level} > 39 &&
        time()-$rps{$_}{lastlogin}>36000 } keys(%rps);
        if (@{$quest{questers}} < 4) { return undef(@{$quest{questers}}); }
        while (@{$quest{questers}} > 4) {
            splice(@{$quest{questers}},int(rand(@{$quest{questers}})),1);
        }
        if (!open(Q,$opts{eventsfile})) {
            return chanmsg("ERROR: Failed to open $opts{eventsfile}: $!");
        }
        my $i;
        while (my $line = <Q>) {
            chomp($line);
            if ($line =~ /^Q/ && rand(++$i) < 1) {
                if ($line =~ /^Q1 (.*)/) {
                    $quest{text} = $1;
                    $quest{type} = 1;
                    $quest{qtime} = time() + 43200 + int(rand(43201)); # 12-24 hours
                }
                elsif ($line =~ /^Q2 (\d+) (\d+) (\d+) (\d+) (.*)/) {
                    $quest{p1} = [$1,$2];
                    $quest{p2} = [$3,$4];
                    $quest{text} = $5;
                    $quest{type} = 2;
                    $quest{stage} = 1;
                }
            }
        }
        close(Q);
        if ($quest{type} == 1) {
            chanmsg(join(", ",(@{$quest{questers}})[0..2]).", and ".
            "$quest{questers}->[3] have been chosen by the gods to ".
            "$quest{text}. Quest to end in ".duration($quest{qtime}-time()).
            ".");
        }
        elsif ($quest{type} == 2) {
        chanmsg(join(", ",(@{$quest{questers}})[0..2]).", and ".
        "$quest{questers}->[3] have been chosen by the gods to ".
        "$quest{text}. Participants must first reach [$quest{p1}->[0],".
        "$quest{p1}->[1]], then [$quest{p2}->[0],$quest{p2}->[1]].".
        ($opts{mapurl}?" See $opts{mapurl} to monitor their journey's ".
            "progress.":""));
        }
        writequestfile();
    }

    public function questpencheck {
        my $k = shift;
        my ($quester,$player);
        for $quester (@{$quest{questers}}) {
            if ($quester eq $k) {
                chanmsg(clog("$k\'s prudence and self-regard has brought the ".
                    "wrath of the gods upon the realm. All your great ".
                    "wickedness makes you as it were heavy with lead, ".
                    "and to tend downwards with great weight and ".
                    "pressure towards hell. Therefore have you drawn ".
                    "yourselves 15 steps closer to that gaping maw."));
                for $player (grep { $rps{$_}{online} } keys %rps) {
                    my $gain = int(15 * ($opts{rppenstep}**$rps{$player}{level}));
                    $rps{$player}{pen_quest} += $gain;
                    $rps{$player}{next} += $gain;
                }
                undef(@{$quest{questers}});
                $quest{qtime} = time() + 43200; # 12 hours
            }
        }
    }

    public function penalize {
        my $username = shift;
        return 0 if !defined($username);
        return 0 if !exists($rps{$username});
        my $type = shift;
        my $pen = 0;
        questpencheck($username);
        if ($type eq "quit") {
            $pen = int(20 * ($opts{rppenstep}**$rps{$username}{level}));
            if ($opts{limitpen} && $pen > $opts{limitpen}) {
                $pen = $opts{limitpen};
            }
            $rps{$username}{pen_quit}+=$pen;
            $rps{$username}{online}=0;
        }
        elsif ($type eq "nick") {
            my $newnick = shift;
            $pen = int(30 * ($opts{rppenstep}**$rps{$username}{level}));
            if ($opts{limitpen} && $pen > $opts{limitpen}) {
                $pen = $opts{limitpen};
            }
            $rps{$username}{pen_nick}+=$pen;
            $rps{$username}{nick} = substr($newnick,1);
            substr($rps{$username}{userhost},0,length($rps{$username}{nick})) =
                substr($newnick,1);
            notice("Penalty of ".duration($pen)." added to your timer for ".
                "nick change.",$rps{$username}{nick});
        }
        elsif ($type eq "privmsg" || $type eq "notice") {
            $pen = int(shift(@_) * ($opts{rppenstep}**$rps{$username}{level}));
            if ($opts{limitpen} && $pen > $opts{limitpen}) {
                $pen = $opts{limitpen};
            }
            $rps{$username}{pen_mesg}+=$pen;
            notice("Penalty of ".duration($pen)." added to your timer for ".
                $type.".",$rps{$username}{nick});
        }
        elsif ($type eq "part") {
            $pen = int(200 * ($opts{rppenstep}**$rps{$username}{level}));
            if ($opts{limitpen} && $pen > $opts{limitpen}) {
                $pen = $opts{limitpen};
            }
            $rps{$username}{pen_part}+=$pen;
            notice("Penalty of ".duration($pen)." added to your timer for ".
                "parting.",$rps{$username}{nick});
            $rps{$username}{online}=0;
        }
        elsif ($type eq "kick") {
            $pen = int(250 * ($opts{rppenstep}**$rps{$username}{level}));
            if ($opts{limitpen} && $pen > $opts{limitpen}) {
                $pen = $opts{limitpen};
            }
            $rps{$username}{pen_kick}+=$pen;
            notice("Penalty of ".duration($pen)." added to your timer for ".
                "being kicked.",$rps{$username}{nick});
            $rps{$username}{online}=0;
        }
        elsif ($type eq "logout") {
            $pen = int(20 * ($opts{rppenstep}**$rps{$username}{level}));
            if ($opts{limitpen} && $pen > $opts{limitpen}) {
                $pen = $opts{limitpen};
            }
            $rps{$username}{pen_logout} += $pen;
            notice("Penalty of ".duration($pen)." added to your timer for ".
                "LOGOUT command.",$rps{$username}{nick});
            $rps{$username}{online}=0;
        }
        $rps{$username}{next} += $pen;
        return 1; # successfully penalized a user! woohoo!
    }

    public function finduser {
        my $nick = shift;
        return undef if !defined($nick);
        for my $user (keys(%rps)) {
            next unless $rps{$user}{online};
            if ($rps{$user}{nick} eq $nick) { return $user; }
        }
        return undef;
    }

    public function ha { # return 0/1 if username has access
        my $user = shift;
        if (!defined($user) || !exists($rps{$user})) {
            debug("Error: Attempted ha() for invalid username \"$user\"");
            return 0;
        }
        return $rps{$user}{isadmin};
    }

    public function checksplits { # removed expired split hosts from the hash
        my $host;
        while ($host = each(%split)) {
            if (time()-$split{$host}{time} > $opts{splitwait}) {
                $rps{$split{$host}{account}}{online} = 0;
                delete($split{$host});
            }
        }
    }

    public function collision_fight {
        my($u,$opp) = @_;
        my $mysum = itemsum($u,1);
        my $oppsum = itemsum($opp,1);
        my $myroll = int(rand($mysum));
        my $opproll = int(rand($oppsum));
        if ($myroll >= $opproll) {
            my $gain = int($rps{$opp}{level}/4);
            $gain = 7 if $gain < 7;
            $gain = int(($gain/100)*$rps{$u}{next});
            chanmsg(clog("$u [$myroll/$mysum] has come upon $opp [$opproll/$oppsum".
                "] and taken them in combat! ".duration($gain)." is ".
                "removed from $u\'s clock."));
            $rps{$u}{next} -= $gain;
            chanmsg("$u reaches next level in ".duration($rps{$u}{next}).".");
            if (rand(35) < 1 && $opp ne $primnick) {
                $gain = int(((5 + int(rand(20)))/100) * $rps{$opp}{next});
                chanmsg(clog("$u has dealt $opp a Critical Strike! ".
                    duration($gain)." is added to $opp\'s clock."));
                $rps{$opp}{next} += $gain;
                chanmsg("$opp reaches next level in ".duration($rps{$opp}{next}).
                    ".");
            }
            elsif (rand(25) < 1 && $opp ne $primnick && $rps{$u}{level} > 19) {
                my @items = ("ring","amulet","charm","weapon","helm","tunic",
                             "pair of gloves","set of leggings","shield",
                             "pair of boots");
                my $type = $items[rand(@items)];
                if (int($rps{$opp}{item}{$type}) > int($rps{$u}{item}{$type})) {
                    chanmsg("In the fierce battle, $opp dropped his level ".
                        int($rps{$opp}{item}{$type})." $type! $u picks it up, ".
                        "tossing his old level ".int($rps{$u}{item}{$type}).
                        " $type to $opp.");
                    my $tempitem = $rps{$u}{item}{$type};
                    $rps{$u}{item}{$type}=$rps{$opp}{item}{$type};
                    $rps{$opp}{item}{$type} = $tempitem;
                }
            }
        }
        else {
            my $gain = ($opp eq $primnick)?10:int($rps{$opp}{level}/7);
            $gain = 7 if $gain < 7;
            $gain = int(($gain/100)*$rps{$u}{next});
            chanmsg(clog("$u [$myroll/$mysum] has come upon $opp [$opproll/$oppsum".
                "] and been defeated in combat! ".duration($gain)." is ".
                "added to $u\'s clock."));
            $rps{$u}{next} += $gain;
            chanmsg("$u reaches next level in ".duration($rps{$u}{next}).".");
        }
    }

    public function writequestfile {
        return unless $opts{writequestfile};
        open(QF,">$opts{questfilename}") or do {
            chanmsg("Error: Cannot open $opts{questfilename}: $!");
            return;
        };
        # if no active quest, just empty questfile. otherwise, write it
        if (@{$quest{questers}}) {
            if ($quest{type}==1) {
                print QF "T $quest{text}\n".
                "Y 1\n".
                "S $quest{qtime}\n".
                "P1 $quest{questers}->[0]\n".
                "P2 $quest{questers}->[1]\n".
                "P3 $quest{questers}->[2]\n".
                "P4 $quest{questers}->[3]\n";
            }
            elsif ($quest{type}==2) {
            print QF "T $quest{text}\n".
            "Y 2\n".
            "S $quest{stage}\n".
            "P $quest{p1}->[0] $quest{p1}->[1] $quest{p2}->[0] ".
            "$quest{p2}->[1]\n".
            "P1 $quest{questers}->[0] $rps{$quest{questers}->[0]}{x} ".
            "$rps{$quest{questers}->[0]}{y}\n".
            "P2 $quest{questers}->[1] $rps{$quest{questers}->[1]}{x} ".
            "$rps{$quest{questers}->[1]}{y}\n".
            "P3 $quest{questers}->[2] $rps{$quest{questers}->[2]}{x} ".
            "$rps{$quest{questers}->[2]}{y}\n".
            "P4 $quest{questers}->[3] $rps{$quest{questers}->[3]}{x} ".
            "$rps{$quest{questers}->[3]}{y}\n";
            }
        }
        close(QF);
    }

    public function goodness {
        my @players = grep { $rps{$_}{alignment} eq "g" &&
        $rps{$_}{online} } keys(%rps);
        return unless @players > 1;
        splice(@players,int(rand(@players)),1) while @players > 2;
        my $gain = 5 + int(rand(8));
        chanmsg(clog("$players[0] and $players[1] have not let the iniquities of ".
            "evil men poison them. Together have they prayed to their ".
            "god, and it is his light that now shines upon them. $gain\% ".
            "of their time is removed from their clocks."));
        $rps{$players[0]}{next} = int($rps{$players[0]}{next}*(1 - ($gain/100)));
        $rps{$players[1]}{next} = int($rps{$players[1]}{next}*(1 - ($gain/100)));
        chanmsg("$players[0] reaches next level in ".
            duration($rps{$players[0]}{next}).".");
        chanmsg("$players[1] reaches next level in ".
            duration($rps{$players[1]}{next}).".");
    }

    public function evilness {
        my @evil = grep { $rps{$_}{alignment} eq "e" &&
        $rps{$_}{online} } keys(%rps);
        return unless @evil;
        my $me = $evil[rand(@evil)];
        if (int(rand(2)) < 1) {
            # evil only steals from good :^(
            my @good = grep { $rps{$_}{alignment} eq "g" &&
            $rps{$_}{online} } keys(%rps);
            my $target = $good[rand(@good)];
            my @items = ("ring","amulet","charm","weapon","helm","tunic",
                         "pair of gloves","set of leggings","shield",
                         "pair of boots");
            my $type = $items[rand(@items)];
            if (int($rps{$target}{item}{$type}) > int($rps{$me}{item}{$type})) {
                my $tempitem = $rps{$me}{item}{$type};
                $rps{$me}{item}{$type} = $rps{$target}{item}{$type};
                $rps{$target}{item}{$type} = $tempitem;
                chanmsg(clog("$me stole $target\'s level ".
                    int($rps{$me}{item}{$type})." $type while they were ".
                    "sleeping! $me leaves his old level ".
                    int($rps{$target}{item}{$type})." $type behind, ".
                    "which $target then takes."));
            }
            else {
                notice("You made to steal $target\'s $type, but realized it was ".
                    "lower level than your own. You creep back into the ".
                    "shadows.",$rps{$me}{nick});
            }
        }
        else { # being evil only pays about half of the time...
            my $gain = 1 + int(rand(5));
            chanmsg(clog("$me is forsaken by his evil god. ".
                duration(int($rps{$me}{next} * ($gain/100)))." is added ".
                "to his clock."));
            $rps{$me}{next} = int($rps{$me}{next} * (1 + ($gain/100)));
            chanmsg("$me reaches next level in ".duration($rps{$me}{next}).".");
        }
    }

    public function fisher_yates_shuffle {
        my $array = shift;
        my $i;
        for ($i = @$array; --$i; ) {
            my $j = int rand ($i+1);
            next if $i == $j;
            @$array[$i,$j] = @$array[$j,$i];
        }
    }

    public function writedb {
        open(RPS,">$opts{dbfile}") or do {
            chanmsg("ERROR: Cannot write $opts{dbfile}: $!");
            return 0;
        };
        print RPS join("\t","# username",
            "pass",
            "is admin",
            "level",
            "class",
            "next ttl",
            "nick",
            "userhost",
            "online",
            "idled",
            "x pos",
            "y pos",
            "pen_mesg",
            "pen_nick",
            "pen_part",
            "pen_kick",
            "pen_quit",
            "pen_quest",
            "pen_logout",
            "created",
            "last login",
            "amulet",
            "charm",
            "helm",
            "boots",
            "gloves",
            "ring",
            "leggings",
            "shield",
            "tunic",
            "weapon",
            "alignment")."\n";
        my $k;
        keys(%rps); # reset internal pointer
        while ($k=each(%rps)) {
            if (exists($rps{$k}{next}) && defined($rps{$k}{next})) {
                print RPS join("\t",$k,
                    $rps{$k}{pass},
                    $rps{$k}{isadmin},
                    $rps{$k}{level},
                    $rps{$k}{class},
                                    $rps{$k}{next},
                                    $rps{$k}{nick},
                                    $rps{$k}{userhost},
                                    $rps{$k}{online},
                                    $rps{$k}{idled},
                                    $rps{$k}{x},
                                    $rps{$k}{y},
                                    $rps{$k}{pen_mesg},
                                    $rps{$k}{pen_nick},
                                    $rps{$k}{pen_part},
                                    $rps{$k}{pen_kick},
                                    $rps{$k}{pen_quit},
                                    $rps{$k}{pen_quest},
                                    $rps{$k}{pen_logout},
                                    $rps{$k}{created},
                                    $rps{$k}{lastlogin},
                                    $rps{$k}{item}{amulet},
                                    $rps{$k}{item}{charm},
                                    $rps{$k}{item}{helm},
                                    $rps{$k}{item}{"pair of boots"},
                                    $rps{$k}{item}{"pair of gloves"},
                                    $rps{$k}{item}{ring},
                                    $rps{$k}{item}{"set of leggings"},
                                    $rps{$k}{item}{shield},
                                    $rps{$k}{item}{tunic},
                                    $rps{$k}{item}{weapon},
                                    $rps{$k}{alignment})."\n";
            }
        }
        close(RPS);
    }

    public function readconfig {
        if (! -e ".irpg.conf") {
            debug("Error: Cannot find .irpg.conf. Copy it to this directory, ".
                "please.",1);
        }
        else {
            open(CONF,"<.irpg.conf") or do {
                debug("Failed to open config file .irpg.conf: $!",1);
            };
            my($line,$key,$val);
            while ($line=<CONF>) {
                next() if $line =~ /^#/; # skip comments
                $line =~ s/[\r\n]//g;
                $line =~ s/^\s+//g;
                    next() if !length($line); # skip blank lines
                ($key,$val) = split(/\s+/,$line,2);
                $key = lc($key);
                if (lc($val) eq "on" || lc($val) eq "yes") { $val = 1; }
                elsif (lc($val) eq "off" || lc($val) eq "no") { $val = 0; }
                if ($key eq "die") {
                    die("Please edit the file .irpg.conf to setup your bot's ".
                        "options. Also, read the README file if you haven't ".
                        "yet.\n");
                }
                elsif ($key eq "server") { push(@{$opts{servers}},$val); }
                elsif ($key eq "okurl") { push(@{$opts{okurl}},$val); }
                else { $opts{$key} = $val; }
            }
        }
    }
}
