.PHONY: server
server:
	php -S 127.0.0.1:9999 -t public public/index.php
