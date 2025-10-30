# Deployment Guide

## Prerequisites

- Node.js 18.x or higher
- npm or yarn
- PostgreSQL 14+ (for production)
- Redis (optional, for rate limiting)
- Domain with SSL certificate

## Environment Setup

### 1. Clone and Install

```bash
git clone <repository-url>
cd warranty-activation
npm install
```

### 2. Configure Environment Variables

Create `.env.local` file:

```bash
# Application
NEXT_PUBLIC_API_URL=https://i-laminat.ru
NEXT_PUBLIC_BASE_URL=https://i-laminat.ru

# Database
DATABASE_URL=postgresql://username:password@host:5432/warranty_db

# Redis (for rate limiting)
REDIS_URL=redis://localhost:6379

# Analytics
NEXT_PUBLIC_GA4_ID=G-XXXXXXXXXX
NEXT_PUBLIC_YANDEX_METRIKA_ID=12345678

# External Services
SMS_API_KEY=your_sms_api_key
EMAIL_API_KEY=your_email_api_key
CRM_API_KEY=your_crm_api_key
CRM_API_URL=https://api.crm.example.com

# Security
NEXT_PUBLIC_CAPTCHA_SITE_KEY=your_site_key
CAPTCHA_SECRET_KEY=your_secret_key

# Yandex Reviews
NEXT_PUBLIC_YANDEX_REVIEWS_URL=https://yandex.ru/maps/org/i_laminat/1234567890/reviews
```

### 3. Database Setup

```bash
# Create database
createdb warranty_db

# Run migrations
psql -d warranty_db -f database/schema.sql
```

### 4. Build Application

```bash
npm run build
```

## Deployment Options

### Option 1: Vercel (Recommended for Next.js)

1. **Install Vercel CLI:**
```bash
npm i -g vercel
```

2. **Login to Vercel:**
```bash
vercel login
```

3. **Deploy:**
```bash
vercel --prod
```

4. **Configure Environment Variables:**
   - Go to Vercel Dashboard → Project Settings → Environment Variables
   - Add all variables from `.env.local`

5. **Configure Database:**
   - Use Vercel Postgres or external PostgreSQL
   - Add DATABASE_URL to environment variables

**Vercel Configuration** (`vercel.json`):
```json
{
  "buildCommand": "npm run build",
  "outputDirectory": ".next",
  "framework": "nextjs",
  "regions": ["arn1"],
  "env": {
    "NEXT_PUBLIC_API_URL": "@api_url",
    "DATABASE_URL": "@database_url"
  }
}
```

---

### Option 2: Docker

1. **Create Dockerfile:**
```dockerfile
FROM node:18-alpine

WORKDIR /app

COPY package*.json ./
RUN npm ci --only=production

COPY . .
RUN npm run build

EXPOSE 3000

CMD ["npm", "start"]
```

2. **Create docker-compose.yml:**
```yaml
version: '3.8'

services:
  app:
    build: .
    ports:
      - "3000:3000"
    environment:
      - DATABASE_URL=postgresql://postgres:password@db:5432/warranty_db
      - REDIS_URL=redis://redis:6379
    depends_on:
      - db
      - redis
    restart: unless-stopped

  db:
    image: postgres:14
    environment:
      - POSTGRES_DB=warranty_db
      - POSTGRES_PASSWORD=password
    volumes:
      - postgres_data:/var/lib/postgresql/data
      - ./database/schema.sql:/docker-entrypoint-initdb.d/schema.sql
    restart: unless-stopped

  redis:
    image: redis:7-alpine
    restart: unless-stopped

volumes:
  postgres_data:
```

3. **Deploy:**
```bash
docker-compose up -d
```

---

### Option 3: VPS/Dedicated Server

1. **Install Dependencies:**
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Install PostgreSQL
sudo apt install -y postgresql postgresql-contrib

# Install Nginx
sudo apt install -y nginx

# Install PM2
sudo npm install -g pm2
```

2. **Setup Application:**
```bash
# Clone repository
cd /var/www
git clone <repository-url> warranty-activation
cd warranty-activation

# Install dependencies
npm ci --only=production

# Build
npm run build

# Setup PM2
pm2 start npm --name "warranty-app" -- start
pm2 save
pm2 startup
```

3. **Configure Nginx:**
```nginx
server {
    listen 80;
    server_name i-laminat.ru www.i-laminat.ru;

    location / {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }
}
```

4. **Enable SSL with Let's Encrypt:**
```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d i-laminat.ru -d www.i-laminat.ru
```

---

## Post-Deployment Checklist

### 1. Verify Application
- [ ] Application loads at https://i-laminat.ru/garantia
- [ ] Form submits successfully
- [ ] Success page displays correctly
- [ ] All validation works
- [ ] Mobile responsive design works

### 2. Database
- [ ] Database schema created
- [ ] Sample data inserted (for testing)
- [ ] Indexes created
- [ ] Backup strategy configured

### 3. Security
- [ ] HTTPS enabled (SSL certificate)
- [ ] CORS configured
- [ ] Rate limiting active
- [ ] CAPTCHA configured
- [ ] Environment variables secure (not in code)
- [ ] Database credentials secure

### 4. Monitoring
- [ ] Error tracking (Sentry, LogRocket)
- [ ] Performance monitoring (New Relic, Datadog)
- [ ] Uptime monitoring (UptimeRobot, Pingdom)
- [ ] Analytics configured (GA4, Yandex Metrika)

### 5. Integrations
- [ ] CRM integration tested
- [ ] SMS notifications working
- [ ] Email notifications working
- [ ] Yandex Reviews links correct

### 6. Testing
- [ ] Test contract activation (IL-123456)
- [ ] Test phone activation (+7 999 123-45-67)
- [ ] Test rate limiting
- [ ] Test error handling
- [ ] Test all form steps
- [ ] Test discount selection
- [ ] Cross-browser testing
- [ ] Mobile device testing

---

## Maintenance

### Regular Tasks

**Daily:**
- Monitor error logs
- Check application uptime
- Review rate limiting logs

**Weekly:**
- Database backup verification
- Performance metrics review
- Security updates check

**Monthly:**
- Dependency updates (`npm audit`, `npm outdated`)
- Database optimization (VACUUM, ANALYZE)
- SSL certificate renewal check
- Review analytics and user feedback

### Backup Strategy

**Database Backup:**
```bash
# Daily automated backup
0 2 * * * pg_dump warranty_db | gzip > /backups/warranty_db_$(date +\%Y\%m\%d).sql.gz

# Keep 30 days of backups
find /backups -name "warranty_db_*.sql.gz" -mtime +30 -delete
```

**Application Backup:**
```bash
# Weekly code backup
0 3 * * 0 tar -czf /backups/app_$(date +\%Y\%m\%d).tar.gz /var/www/warranty-activation
```

### Monitoring Setup

**PM2 Monitoring:**
```bash
pm2 install pm2-logrotate
pm2 set pm2-logrotate:max_size 10M
pm2 set pm2-logrotate:retain 7
```

**Error Tracking (Sentry):**
```bash
npm install @sentry/nextjs
```

Add to `next.config.js`:
```javascript
const { withSentryConfig } = require('@sentry/nextjs');

module.exports = withSentryConfig({
  // ... existing config
}, {
  silent: true,
  org: 'your-org',
  project: 'warranty-activation',
});
```

---

## Scaling

### Horizontal Scaling

**Load Balancer (Nginx):**
```nginx
upstream warranty_app {
    least_conn;
    server 10.0.0.1:3000;
    server 10.0.0.2:3000;
    server 10.0.0.3:3000;
}

server {
    listen 80;
    server_name i-laminat.ru;

    location / {
        proxy_pass http://warranty_app;
        # ... proxy headers
    }
}
```

### Database Optimization

**Connection Pooling:**
```javascript
import { Pool } from 'pg';

const pool = new Pool({
  connectionString: process.env.DATABASE_URL,
  max: 20,
  idleTimeoutMillis: 30000,
  connectionTimeoutMillis: 2000,
});
```

**Indexes:**
```sql
CREATE INDEX CONCURRENTLY idx_warranties_contract_activated 
ON warranties(contract_id, activated_at);
```

### Caching Strategy

**Redis Caching:**
```javascript
import Redis from 'ioredis';

const redis = new Redis(process.env.REDIS_URL);

// Cache discount catalog
await redis.setex('discounts', 3600, JSON.stringify(discounts));
```

---

## Rollback Procedure

If deployment fails:

1. **Vercel:** Instant rollback in dashboard
2. **Docker:** `docker-compose down && git checkout <previous-commit> && docker-compose up -d`
3. **PM2:** `git checkout <previous-commit> && npm run build && pm2 restart warranty-app`

---

## Troubleshooting

### Application Won't Start
```bash
# Check logs
pm2 logs warranty-app

# Check Node version
node --version

# Rebuild
rm -rf .next node_modules
npm install
npm run build
```

### Database Connection Issues
```bash
# Test connection
psql -h localhost -U postgres -d warranty_db

# Check credentials
echo $DATABASE_URL
```

### High Memory Usage
```bash
# Increase PM2 memory limit
pm2 start npm --name "warranty-app" --max-memory-restart 1G -- start
```

---

## Support Contacts

- DevOps Team: devops@i-laminat.ru
- On-Call: +7 (XXX) XXX-XX-XX
- Slack: #warranty-activation
- Documentation: https://wiki.i-laminat.ru/warranty-activation
