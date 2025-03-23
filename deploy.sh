#!/bin/bash
cd /var/www/html/spend-wise

# Pull latest changes (but keep .env intact)
git pull origin main

# Set correct permissions
chmod -R 775 var/cache var/log

# Install dependencies
composer install --no-dev --optimize-autoloader

# Clear and warm up cache
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

# Run migrations
php bin/console doctrine:migrations:migrate --no-interaction

# Restart Apache (if needed)
sudo systemctl restart apache2

echo "🚀 Deployment complete!"
