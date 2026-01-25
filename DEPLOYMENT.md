# Thai Developer Docs - Deployment Guide

## Prerequisites

- Ubuntu 22.04+ server with Docker and Docker Compose
- Domain names configured:
  - `thaidevdocs.com` - Frontend
  - `api.thaidevdocs.com` - Backend API
  - `admin.thaidevdocs.com` - Admin panel
- SSL certificates (Let's Encrypt recommended)
- Minimum 4GB RAM, 2 vCPUs

## Quick Start

### 1. Clone the repository

```bash
git clone https://github.com/your-org/thaidevdocs.git /opt/thaidevdocs
cd /opt/thaidevdocs
```

### 2. Configure environment

```bash
cp .env.production.example .env
# Edit .env with your production values
nano .env
```

### 3. Set up SSL certificates

Using Let's Encrypt with Certbot:

```bash
sudo apt install certbot
sudo certbot certonly --standalone -d thaidevdocs.com -d www.thaidevdocs.com -d api.thaidevdocs.com -d admin.thaidevdocs.com

# Copy certificates
mkdir -p docker/nginx/ssl
sudo cp /etc/letsencrypt/live/thaidevdocs.com/fullchain.pem docker/nginx/ssl/
sudo cp /etc/letsencrypt/live/thaidevdocs.com/privkey.pem docker/nginx/ssl/
```

### 4. Build and start services

```bash
# Build images
docker-compose -f docker-compose.prod.yml build

# Start services
docker-compose -f docker-compose.prod.yml up -d

# Run migrations
docker-compose -f docker-compose.prod.yml exec backend php artisan migrate --force

# Create admin user
docker-compose -f docker-compose.prod.yml exec backend php artisan tinker
# >>> \App\Models\User::create(['name' => 'Admin', 'email' => 'admin@thaidevdocs.com', 'password' => bcrypt('secure_password'), 'role' => 'admin']);

# Seed initial data (if available)
docker-compose -f docker-compose.prod.yml exec backend php artisan db:seed --class=ProductionSeeder

# Sync Meilisearch indexes
docker-compose -f docker-compose.prod.yml exec backend php artisan scout:sync-index-settings
docker-compose -f docker-compose.prod.yml exec backend php artisan scout:import "App\Models\Article"
docker-compose -f docker-compose.prod.yml exec backend php artisan scout:import "App\Models\Glossary"

# Generate sitemap
docker-compose -f docker-compose.prod.yml exec backend php artisan sitemap:generate
```

## CI/CD with GitHub Actions

### Required Secrets

Configure these in your GitHub repository settings:

| Secret | Description |
|--------|-------------|
| `SERVER_HOST` | Production server IP or hostname |
| `SERVER_USER` | SSH user for deployment |
| `SSH_PRIVATE_KEY` | SSH private key for authentication |
| `SLACK_WEBHOOK` | (Optional) Slack webhook for notifications |

### Automatic Deployment

1. Push to `main` branch triggers production deployment
2. Pull requests run CI checks (lint, test, build)
3. Develop branch runs CI only

## Service Management

### View logs

```bash
# All services
docker-compose -f docker-compose.prod.yml logs -f

# Specific service
docker-compose -f docker-compose.prod.yml logs -f backend
docker-compose -f docker-compose.prod.yml logs -f nginx
docker-compose -f docker-compose.prod.yml logs -f horizon
```

### Restart services

```bash
# All services
docker-compose -f docker-compose.prod.yml restart

# Specific service
docker-compose -f docker-compose.prod.yml restart backend
```

### Update deployment

```bash
cd /opt/thaidevdocs
git pull origin main
docker-compose -f docker-compose.prod.yml pull
docker-compose -f docker-compose.prod.yml up -d --remove-orphans
docker-compose -f docker-compose.prod.yml exec backend php artisan migrate --force
docker-compose -f docker-compose.prod.yml exec backend php artisan cache:clear
docker-compose -f docker-compose.prod.yml exec backend php artisan config:cache
```

## Backup Strategy

### Database backup

```bash
# Create backup
docker-compose -f docker-compose.prod.yml exec db mysqldump -u root -p thaidevdocs > backup_$(date +%Y%m%d).sql

# Automated daily backup (add to crontab)
0 2 * * * cd /opt/thaidevdocs && docker-compose -f docker-compose.prod.yml exec -T db mysqldump -u root -p$DB_ROOT_PASSWORD thaidevdocs | gzip > /backup/thaidevdocs_$(date +\%Y\%m\%d).sql.gz
```

### Media files backup

```bash
# If using local storage
tar -czf media_backup_$(date +%Y%m%d).tar.gz backend/storage/app/public

# If using S3, use AWS CLI
aws s3 sync s3://thaidevdocs-media /backup/media --profile thaidevdocs
```

## Monitoring

### Health checks

- Backend: `https://api.thaidevdocs.com/api/v1/health`
- Meilisearch: `http://localhost:7700/health`
- Redis: `docker-compose -f docker-compose.prod.yml exec redis redis-cli ping`

### Laravel Horizon

Access Horizon dashboard at `https://api.thaidevdocs.com/horizon` (requires admin authentication).

## Scaling

### Horizontal scaling

For high traffic, consider:

1. **Load balancer**: Use Nginx upstream or cloud load balancer
2. **Multiple app instances**: Scale backend service
3. **Database replica**: MySQL read replicas
4. **CDN**: CloudFlare or AWS CloudFront for static assets

### Scaling example

```yaml
# docker-compose.prod.yml
services:
  backend:
    deploy:
      replicas: 3
      resources:
        limits:
          cpus: '1'
          memory: 1G
```

## Troubleshooting

### Common issues

**502 Bad Gateway**
- Check if backend container is running: `docker-compose -f docker-compose.prod.yml ps`
- Check PHP-FPM logs: `docker-compose -f docker-compose.prod.yml logs backend`

**Database connection failed**
- Verify DB_HOST, DB_USERNAME, DB_PASSWORD in .env
- Check MySQL container: `docker-compose -f docker-compose.prod.yml logs db`

**Search not working**
- Check Meilisearch status: `docker-compose -f docker-compose.prod.yml logs meilisearch`
- Re-import indexes: `docker-compose -f docker-compose.prod.yml exec backend php artisan scout:import "App\Models\Article"`

**Queue jobs not processing**
- Check Horizon: `docker-compose -f docker-compose.prod.yml logs horizon`
- Restart Horizon: `docker-compose -f docker-compose.prod.yml restart horizon`

## Security Checklist

- [ ] Strong passwords for database, Redis, and Meilisearch
- [ ] SSL certificates properly configured
- [ ] Firewall rules (only 80/443 exposed)
- [ ] Regular security updates
- [ ] Backup encryption
- [ ] Rate limiting enabled
- [ ] Admin 2FA enabled
- [ ] Log monitoring configured
