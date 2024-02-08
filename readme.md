[mydreamday.fi](https://mydreamday.fi) (Magento 2.4.6).

## How to deploy the static content
```
bin/magento maintenance:enable
bin/magento cache:clean
rm -rf pub/static/* var/cache var/page_cache var/view_preprocessed
bin/magento setup:static-content:deploy \
	--area adminhtml \
	--theme Magento/backend \
	-f en_US fi_FI sv_SE
bin/magento setup:static-content:deploy \
	--area frontend \
	--theme Mydreamday/snappy \
	-f en_US fi_FI sv_SE
bin/magento cache:clean
bin/magento maintenance:disable
```