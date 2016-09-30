<?php //declare(strict_types = 1);

namespace SevenPercent\Proviso;

use Exception;
use Symfony\Component\Console;

class CreateCommand extends Console\Command\Command {

	protected function configure() {
		$this->setName('create');
	}

	protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output) {
		$config = new Config(getcwd());
		// create vm first just to check that there isn't a name conflict(?) and let VBoxManage create basefolder for us if it doesn't already exist
		VirtualBoxManager::executeCommand([
			'createvm',
		], [
			'basefolder' => $config->getBasePath(),
			'name' => $config->getName(),
			'ostype' => 'RedHat_64',
			'register' => NULL,
		]);
		// set up kickstart
		if (!is_dir($config->getKickstartPath())) {
			mkdir($config->getKickstartPath());
		}
		file_put_contents($config->getKickstartConfigFilename(), implode(PHP_EOL, [
			'auth --enableshadow --passalgo=\'sha512\'',
			'autopart --fstype=\'ext4\' --type=\'lvm\'',
			'bootloader --boot-drive=\'sda\' --location=\'mbr\' --timeout=\'1\'',
			'cdrom',
			'clearpart --all --initlabel',
			'firewall --disabled',
			'firstboot --disabled',
			'group --gid=\'2000\' --name=\'sysadmin\'',
			'group --gid=\'2001\' --name=\'devops\'',
			'group --gid=\'2002\' --name=\'webdev\'',
			'ignoredisk --only-use=\'sda\'',
			'install',
			'keyboard --vckeymap=\'gb-mac\'',
			'lang en_GB.UTF-8',
			'logging --level=\'warning\'',
			'network --activate --bootproto=\'dhcp\' --device=\'enp0s17\' --noipv6 --onboot=\'yes\'',
			'network --activate --bootproto=\'dhcp\' --device=\'enp0s8\' --noipv6 --onboot=\'yes\'',
			'network --hostname=\'' . $config->getName() . '\'',
			'rootpw --iscrypted $6$jKsGjvFT$OcSpfa9kTn.5Dg5rUO1luM71kH9ctQqjyA7MrxZ4w16qUT1c5CXf9NK9GejmxYWhXanHeCk0z2VRWP6w5J43M0',
			'selinux --permissive',
			'services --enabled=\'chronyd\'',
			'shutdown',
			'skipx',
			'text',
			'timezone --isUtc --ntpservers=\'0.centos.pool.ntp.org,1.centos.pool.ntp.org.2.centos.pool.ntp.org,3.centos.pool.ntp.org\' Etc/UTC',
			'unsupported_hardware',
			'user --gecos=\'Steven Hilder\' --gid=\'2000\' --groups=\'wheel,sysadmin,devops,webdev,users\' --iscrypted --name=\'steven.hilder\' --password=\'$6$43nVaHUV$4APz4FrchS34YiYVtSuPg3WqHJlsNkegyoaFgd7Zileh7QTO2LEHDSSjJz8G3VNbPlJQ2YoVfAj0Zp1BeplQK1\' --uid=\'2500\'',
			'zerombr',
			'',
			'%packages --nobase',
			'@core --nodefaults',
			'-aic*',
			'-alsa*',
			'-avahi*',
			'-btrfs*',
			'-dosfstools',
			'-iprutils',
			'-ivtv*',
			'-iwl*',
			'-kexec-tools',
			'-kmod*',
			'-libselinux-python',
			'-mariadb-libs',
			'-microcode_ctl',
			'-plymouth*',
			'-postfix',
			'-python-slip*',
			'-qrencode-libs',
			'-trousers',
			'-tuned',
			'-wpa_supplicant',
			'-xfsprogs',
			'chrony',
			'firewalld',
			'%end',
			'',
			'%addon com_redhat_kdump --disable',
			'%end',
		]));
		exec(escapeshellcmd('hdiutil makehybrid -iso -iso-volume-name OEMDRV -o ' . escapeshellarg($config->getKickstartImageFilename()) . ' ' . escapeshellarg($config->getKickstartPath())) . ' 2>&1', $output, $exitCode);
		if ($exitCode !== 0) {
			throw new Exception('hdiutil failed');
		}
		//
		VirtualBoxManager::executeCommand([
			'modifyvm',
			$config->getName(),
		], [
			'memory' => '512',
			'vram' => '32',
			'acpi' => 'on',
			'ioapic' => 'on',
			'hpet' => 'on',
			'triplefaultreset' => 'off',
			'paravirtprovider' => 'default',
			'hwvirtex' => 'on',
			'nestedpaging' => 'on',
			'largepages' => 'on',
			'vtxvpid' => 'on',
			'vtxux' => 'on',
			'pae' => 'on',
			'longmode' => 'on',
			'cpus' => '1',
			'cpuhotplug' => 'off',
			'cpuexecutioncap' => '100',
			'rtcuseutc' => 'on',
			'graphicscontroller' => 'vboxvga',
			'monitorcount' => '1',
			'accelerate3d' => 'off',
			'accelerate2dvideo' => 'off',
			'firmware' => 'efi64',
			'chipset' => 'piix3',
			'bioslogofadein' => 'off',
			'bioslogofadeout' => 'off',
			'bioslogodisplaytime' => '0',
			'biosbootmenu' => 'disabled',
			'biossystemtimeoffset' => '0',
			'biospxedebug' => 'off',
			'boot1' => 'dvd',
			'boot2' => 'disk',
			'boot3' => 'none',
			'boot4' => 'none',
			'nic1' => 'natnetwork',
			'nictype1' => '82545EM',
			'cableconnected1' => 'on',
			'nictrace1' => 'off',
			'nicpromisc1' => 'allow-all',
			'nat-network1' => 'ProvisoNatNetwork',
			'macaddress1' => 'auto',
			'nic2' => 'hostonly',
			'nictype2' => '82545EM',
			'cableconnected2' => 'on',
			'nictrace2' => 'off',
			'nicpromisc2' => 'allow-all',
			'hostonlyadapter2' => 'vboxnet2',
			'macaddress2' => 'auto',
			'nic3' => 'none',
			'nic4' => 'none',
			'mouse' => 'ps2',
			'keyboard' => 'ps2',
			'uart1' => 'off',
			'uart2' => 'off',
			'audio' => 'none',
			'clipboard' => 'disabled',
			'draganddrop' => 'disabled',
			'vrde' => 'off',
			'usb' => 'off',
			'snapshotfolder' => 'default',
			'teleporter' => 'off',
			'tracing-enabled' => 'off',
			'usbcardreader' => 'off',
			'autostart-enabled' => 'off',
			'videocap' => 'off',
			'defaultfrontend' => 'gui',
		]);
		VirtualBoxManager::executeCommand([
			'storagectl',
			$config->getName(),
		], [
			'add' => 'sata',
			'bootable' => 'on',
			'controller' => 'IntelAhci',
			'hostiocache' => 'off',
			'name' => 'SATA',
			'portcount' => '3',
		]);
		VirtualBoxManager::executeCommand([
			'createmedium',
			'disk',
		], [
			'filename' => $config->getSSDImageFilename(),
			'format' => 'VDI',
			'sizebyte' => '42949672960',
			'variant' => 'Standard',
		]);
		VirtualBoxManager::executeCommand([
			'storageattach',
			$config->getName(),
		], [
			'hotpluggable' => 'off',
			'medium' => $config->getSSDImageFilename(),
			'mtype' => 'normal',
			'nonrotational' => 'on',
			'port' => '0',
			'storagectl' => 'SATA',
			'type' => 'hdd',
		]);
		VirtualBoxManager::executeCommand([
			'storageattach',
			$config->getName(),
		], [
			'medium' => '/Users/steven.hilder/Developer/CentOS-7-x86_64-Minimal-1511.iso',
			'port' => '1',
			'storagectl' => 'SATA',
			'type' => 'dvddrive',
		]);
		VirtualBoxManager::executeCommand([
			'storageattach',
			$config->getName(),
		], [
			'medium' => $config->getKickstartImageFilename(),
			'port' => '2',
			'storagectl' => 'SATA',
			'type' => 'dvddrive',
		]);
		VirtualBoxManager::executeCommand([
			'startvm',
			$config->getName(),
		], [
			'type' => 'gui',
		]);
		do {
			sleep(2);
			$output = VirtualBoxManager::executeCommand([
				'showvminfo',
				$config->getName(),
			], [
				'machinereadable' => NULL,
			]);
			$found = FALSE;
			$state = NULL;
			foreach ($output as $line) {
				if (preg_match('/^VMState="(.*)"/', $line, $matches) === 1) {
					$found = TRUE;
					$state = $matches[1];
					break;
				}
			}
			if (!$found) {
				throw new Exception('showvminfo produced unreadable output');
			}
		} while ($state === 'running');
		VirtualBoxManager::executeCommand([
			'storageattach',
			$config->getName(),
		], [
			'medium' => 'none',
			'port' => '2',
			'storagectl' => 'SATA',
		]);
		VirtualBoxManager::executeCommand([
			'closemedium',
			'dvd',
			$config->getKickstartImageFilename(),
		], []);
		VirtualBoxManager::executeCommand([
			'storageattach',
			$config->getName(),
		], [
			'medium' => 'none',
			'port' => '1',
			'storagectl' => 'SATA',
		]);
	}
}
