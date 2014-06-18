#!/bin/bash 
# ----------------------------------
source /etc/MySB/inc/includes_before
# ----------------------------------
#  __/\\\\____________/\\\\___________________/\\\\\\\\\\\____/\\\\\\\\\\\\\___        
#   _\/\\\\\\________/\\\\\\_________________/\\\/////////\\\_\/\\\/////////\\\_       
#    _\/\\\//\\\____/\\\//\\\____/\\\__/\\\__\//\\\______\///__\/\\\_______\/\\\_      
#     _\/\\\\///\\\/\\\/_\/\\\___\//\\\/\\\____\////\\\_________\/\\\\\\\\\\\\\\__     
#      _\/\\\__\///\\\/___\/\\\____\//\\\\\________\////\\\______\/\\\/////////\\\_    
#       _\/\\\____\///_____\/\\\_____\//\\\____________\////\\\___\/\\\_______\/\\\_   
#        _\/\\\_____________\/\\\__/\\_/\\\______/\\\______\//\\\__\/\\\_______\/\\\_  
#         _\/\\\_____________\/\\\_\//\\\\/______\///\\\\\\\\\\\/___\/\\\\\\\\\\\\\/__ 
#          _\///______________\///___\////__________\///////////_____\/////////////_____
#			By toulousain79 ---> https://github.com/toulousain79/
#
######################################################################
#
#	Copyright (c) 2013 toulousain79 (https://github.com/toulousain79/)
#	Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
#	The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
#	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
#	IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
#	--> Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
#
##################### FIRST LINE #####################################
#
# Usage:	FirewallAndSecurity {clean|new}
#
##################### FIRST LINE #####################################

case $1 in
	clean)
		# Vidage et suppression des règles existantes :
		log_daemon_msg "Emptying and removal of existing rules"
		for TABLE in filter nat mangle; do
			iptables -t $TABLE -F
			iptables -t $TABLE -X
		done
		StatusLSB
		
		log_daemon_msg "Authorize any incoming connection any outgoing connection"
		iptables -t filter -P INPUT ACCEPT
		iptables -t filter -P FORWARD ACCEPT
		iptables -t filter -P OUTPUT ACCEPT	
		StatusLSB
		
		if [ -f /etc/fail2ban/jail.local ]; then
			service fail2ban stop
		fi		
		
		if [ -f /etc/pgl/pglcmd.conf ]; then
			pglcmd stop
		fi		
	;;
	new)
		# NO spoofing
		if [ -e /proc/sys/net/ipv4/conf/all/rp_filter ]; then
			log_daemon_msg "No spoofing"
			for filtre in /proc/sys/net/ipv4/conf/*/rp_filter; do
				echo 1 > $filtre
			done
			StatusLSB
		fi
		
		# Modules
		log_daemon_msg "Loading modules"
		MODULES="	tun
					iptable_filter
					iptable_nat
					iptable_mangle
					ip_gre
					ip_tables
					ip_nat_ftp
					ip_nat_irc
					ip_conntrack
					ip_conntrack_ftp
					ip_conntrack_irc
					ipt_REJECT
					ipt_tos
					ipt_TOS
					ipt_limit
					ipt_multiport
					ipt_TCPMSS
					ipt_tcpmss
					ipt_ttl
					ipt_length
					ipt_LOG
					ipt_conntrack
					ipt_helper
					ipt_state
					ipt_recent
					ipt_owner
					ipt_mark
					ipt_REDIRECT
					ipt_MASQUERADE
					ipt_MARK
					xt_connlimit
					xt_limit
					xt_multiport
					xt_state
					xt_owner
					xt_NFQUEUE"
					
		for item in $MODULES; do
			IfExist=`lsmod | grep "$item"`
			if [ $? -eq 0 ] ; then
				modprobe $item
			fi
		done
		StatusLSB

		# Vidage et suppression des règles existantes :
		log_daemon_msg "Emptying and removal of existing rules"
		for TABLE in filter nat mangle; do
			iptables -t $TABLE -F
			iptables -t $TABLE -X
		done
		StatusLSB

		# Interdire toute connexion entrante et autoriser toute connexion sortante
		log_daemon_msg "Prohibit any incoming connection and authorize any outgoing connection"
		iptables -t filter -P INPUT DROP
		iptables -t filter -P FORWARD DROP
		iptables -t filter -P OUTPUT ACCEPT	
		StatusLSB
		
		# Ne pas casser les connexions etablies
		log_daemon_msg "Do not break established connections"
		iptables -A INPUT -m state --state RELATED,ESTABLISHED -j ACCEPT
		iptables -A OUTPUT -m state --state RELATED,ESTABLISHED -j ACCEPT	
		StatusLSB
		
		# Autoriser loopback
		log_daemon_msg "Allow loopback interface"
		iptables -t filter -A INPUT -i lo -j ACCEPT
		iptables -t filter -A OUTPUT -o lo -j ACCEPT
		StatusLSB	

		# ICMP
		log_daemon_msg "Allow incoming ping"
		iptables -t filter -A INPUT -p icmp -j ACCEPT -m comment --comment "ICMP"
		StatusLSB

		# CakeBox
		if [ "$INSTALLCAKEBOX" == "YES" ]; then
			log_daemon_msg "Allow access to CakeBox"
			iptables -t filter -A INPUT -p tcp --dport $CAKEBOXPORT -j ACCEPT -m comment --comment "CakeBox"
			StatusLSB
		fi
		
		# HTTP
		log_daemon_msg "Allow access to HTTP"
		iptables -t filter -A INPUT -p tcp --dport $NGINXHTTPPORT -j ACCEPT -m comment --comment "HTTP"
		StatusLSB

		# HTTPS
		log_daemon_msg "Allow access to HTTPs"
		iptables -t filter -A INPUT -p tcp --dport $NGINXHTTPSPORT -j ACCEPT -m comment --comment "HTTPs"
		StatusLSB

		# Webmin
		if [ "$INSTALLWEBMIN" == "YES" ]; then
			log_daemon_msg "Allow access to Webmin"
			iptables -t filter -A INPUT -p tcp --dport $WEBMINPORT -j ACCEPT -m comment --comment "Webmin"
			StatusLSB
		fi		

		# FTP
		log_daemon_msg "Allow use of FTP"
		iptables -t filter -A INPUT -p tcp --dport $NEWFTPPORT -j ACCEPT -m comment --comment "FTP"
		iptables -t filter -A INPUT -p tcp --dport $NEWFTPDATAPORT -j ACCEPT -m comment --comment "FTP Data"
		iptables -t filter -A INPUT -p tcp --dport 65000:65535 -j ACCEPT -m comment --comment "FTP Passive"
		StatusLSB		

		# SSH
		log_daemon_msg "Allow access to SSH"
		iptables -t filter -A INPUT -p tcp --dport $NEWSSHPORT -j ACCEPT -m comment --comment "SSH"
		StatusLSB
		
		# OpenVPN
		if [ "$INSTALLOPENVPN" == "YES" ]; then
			log_daemon_msg "Allow use of OpenVPN"
			iptables -t filter -A INPUT -i tap0 -j ACCEPT
			iptables -t filter -A INPUT -p $OPENVPNPROTO --dport $OPENVPNPORT -j ACCEPT -m comment --comment "OpenVPN"
			iptables -t filter -A INPUT -p $OPENVPNPROTO --dport 8894 -j ACCEPT -m comment --comment "OpenVPN"
			iptables -t filter -I FORWARD -i tun0 -o $PRIMARYINET -s 10.159.12.0/24 -m conntrack --ctstate NEW -j ACCEPT -m comment --comment "OpenVPN"
			iptables -t filter -I FORWARD -m conntrack --ctstate RELATED,ESTABLISHED -j ACCEPT -m comment --comment "OpenVPN"
			iptables -t nat -I POSTROUTING -s 10.159.12.0/24 -j MASQUERADE -m comment --comment "OpenVPN"
			# DLNA
			log_daemon_msg "Allow DLNA"
			iptables -t filter -A INPUT -i tap0 -p tcp --dport 49200 -j ACCEPT -m comment --comment "DLNA"
			iptables -t filter -A INPUT -i tap0 -p udp --dport 49200 -j ACCEPT -m comment --comment "DLNA"
			StatusLSB			
			
			StatusLSB
		fi

		# Mail SMTP:25
	#	log_daemon_msg "Allow use of SMTP"
	#	iptables -t filter -A INPUT -p tcp --dport 25 -j ACCEPT -m comment --comment "SMTP In"
	#	StatusLSB
		
		#### rTorrent
		IGNOREIP="127.0.0.1/8 10.159.12.0/24 192.168.254.0/24"
		WHITELIST="127.0.0.1 10.159.12.0 192.168.254.0"
		LISTUSERS=`ls /etc/MySB/users/ | grep '.info' | sed 's/.\{5\}$//'`
		for seedUser in $LISTUSERS; do
			log_daemon_msg "Allow use of rTorrent for $seedUser"
			
			USERIP=$(cat /etc/MySB/users/$seedUser.info | grep "IP Address=" | awk '{ print $3 }')
			PORT_START=$(cat /etc/MySB/users/$seedUser.info | grep "SCGI port=" | awk '{ print $3 }')
			PORT_END=$(cat /etc/MySB/users/$seedUser.info | grep "rTorrent port=" | awk '{ print $3 }')
			iptables -t filter -A INPUT -p tcp --dport $PORT_START:$PORT_END -j ACCEPT -m comment --comment "rTorrent $seedUser"

			IFS=$','
				for ip in $USERIP; do 
					TEMP="$TEMP $ip/32"
					TEMP2="$TEMP2 $ip"
				done
			unset IFS
	
			StatusLSB
		done
		IGNOREIP="$IGNOREIP `echo $TEMP | sed -e "s/^//g;"`"
		WHITELIST="$WHITELIST `echo $TEMP2 | sed -e "s/^//g;"`"		

		#### NginX
		if [ -f /etc/nginx/locations/MySB.conf ]; then
			for seedUser in $LISTUSERS; do
				log_daemon_msg "Allow access to web server for $seedUser"
				
				USERIP=$(cat /etc/MySB/users/$seedUser.info | grep "IP Address=" | awk '{ print $3 }')

				IFS=$','
				for ip in $USERIP; do 
					SEARCH=$(cat /etc/nginx/locations/MySB.conf | grep $ip)
					if [ -z $SEARCH ]; then
						awk '{ print } /allow 127.0.1.1;/ { print "                allow <ip>;" }' /etc/nginx/locations/MySB.conf > /etc/MySB/files/MySB_location.conf
						perl -pi -e "s/<ip>/$ip/g" /etc/MySB/files/MySB_location.conf
						mv /etc/MySB/files/MySB_location.conf /etc/nginx/locations/MySB.conf
					fi
				done
				unset IFS
				
				StatusLSB
			done
		fi
		
		#### Fail2Ban
		if [ -f /etc/fail2ban/jail.local ]; then
			log_daemon_msg "Add whitelist to Fail2Ban"
		
			SEARCH=$(cat /etc/fail2ban/jail.local | grep "ignoreip =" | cut -d "=" -f 2)
			SEARCH=`echo $SEARCH | sed s,/,\\\\\\\\\\/,g`
			IGNOREIP=`echo $IGNOREIP | sed s,/,\\\\\\\\\\/,g`

			perl -pi -e "s/$SEARCH/$IGNOREIP/g" /etc/fail2ban/jail.local
			
			StatusLSB
		fi
		
		#### PeerGuardian
		#if [ "$MYBLOCKLIST" == "PeerGuardian" ]; then
		if [ -f /etc/pgl/pglcmd.conf ]; then
			log_daemon_msg "Add whitelist to PeerGuardian"
			
			SEARCH=$(cat /etc/pgl/pglcmd.conf | grep "WHITE_IP_IN=" | cut -d "=" -f 2)
		
			perl -pi -e "s/$SEARCH/\"$WHITELIST\"/g" /etc/pgl/pglcmd.conf
			
			StatusLSB
		fi
	;;
	
	*)
		echo -e "${CBLUE}Usage:$CEND	${CYELLOW}bash /etc/MySB/scripts/FirewallAndSecurity.sh$CEND ${CGREEN}{clean|new}$CEND"
		exit	
	;;
esac

# -----------------------------------------
source /etc/MySB/inc/includes_after
# -----------------------------------------
##################### LAST LINE ######################################