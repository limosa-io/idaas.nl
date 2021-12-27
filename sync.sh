#/bin/sh

while true; do 
rsync -ah /Users/arie/Documents/git/laravel-oidc/ ./vendor/arietimmerman/passport/
rsync -ah /Users/arie/Documents/git/laravel-authchain/ ./vendor/arietimmerman/laravel-authchain/
rsync -ah /Users/arie/Documents/git/laravel-authchain-facebook/ ./vendor/arietimmerman/laravel-authchain-facebook/
rsync -ah /Users/arie/Documents/git/laravel-scim-server/ ./vendor/arietimmerman/laravel-scim-server/
rsync -ah /Users/arie/Documents/git/samlidp/ ./vendor/arietimmerman/laravel-saml/
sleep 2
done