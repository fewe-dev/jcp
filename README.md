# JSON configuration parser

This is a simple application to parse JSON configuration files with complex structures.

## Installation

### Debian

**Latest:**
```bash
cd /tmp && curl -sLO https://raw.githubusercontent.com/fewe-dev/jcp/refs/heads/master/build/linux/jcp.deb && sudo dpkg -i jcp.deb
```

**Specific version:**
```bash
cd /tmp && curl -sLO https://raw.githubusercontent.com/fewe-dev/jcp/refs/tags/1.0.0/build/linux/jcp.deb && sudo dpkg -i jcp.deb
```

## Development

### Phar ###

**Install**
```bash
composer global require humbug/box
```
**Compile**
```bash
~/.config/composer/vendor/bin/box compile
```

### Binary ###

**Install**
```bash
composer global require phpacker/phpacker
```
**Compile**
```bash
~/.config/composer/vendor/bin/phpacker build all --src=./build/jcp.phar --dest=./build/
```

### Debian package ###

**Compile**
```bash
cd build/linux && mkdir -p debian/usr/bin && cp linux-x64 debian/usr/bin/jcp && dpkg-deb --build debian jcp.deb && rm -rf debian/usr && cd ../..
```
