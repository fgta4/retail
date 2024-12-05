#!/bin/bash

# rv: RV/05/GEX/WH-JKT/240000029


./importrv.sh \
                -c "/var/www/html/cli.php" \
				-n process-syncrv \
				-p 652e2db1f32dd \
				-u 5effbb0a0f7d1 \
				-d "{\"region_id\":\"03400\",\"hemoving_id\":\"RV/05/GEX/WH-JKT/240000029\",\"merchrv_id\":\"RV/GEX/24120029\"}" \
				-l "/mnt/ramdisk/log-20231017-652e2db1f32dd.txt"

