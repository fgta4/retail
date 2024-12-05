#!/bin/bash

# , \"merchorderout_id\":\"PO/GEX/24120029\"


./importregtopo.sh \
                -c "/var/www/html/cli.php" \
				-n process-syncorder \
				-p 652e2db1f32dd \
				-u 5effbb0a0f7d1 \
				-d "{\"region_id\":\"03400\",\"heinvregister_id\":\"1723087772\",\"merchorderout_id\":\"PO/GEX/24120029\"}" \
				-l "/mnt/ramdisk/log-20231017-652e2db1f32dd.txt"

