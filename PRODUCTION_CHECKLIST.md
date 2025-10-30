# Production Deployment Checklist

Use this checklist when deploying to production.

## Pre-Deployment

### Code Review
- [ ] All TypeScript errors resolved
- [ ] No console.log statements (except intentional)
- [ ] No hardcoded credentials
- [ ] No TODO/FIXME comments blocking release
- [ ] Code reviewed by at least one other developer

### Testing
- [ ] All manual test scenarios passed
- [ ] Mobile testing completed (iOS + Android)
- [ ] Cross-browser testing (Chrome, Firefox, Safari, Edge)
- [ ] Form validation tested thoroughly
- [ ] API endpoints tested with various inputs
- [ ] Error states tested and display correctly
- [ ] Rate limiting tested and works
- [ ] Analytics events firing correctly

### Security
- [ ] All environment variables in `.env.local` (not in code)
- [ ] Database credentials secured
- [ ] API keys secured
- [ ] HTTPS certificate obtained and configured
- [ ] CORS headers configured for production domain
- [ ] Rate limiting configured with Redis
- [ ] CAPTCHA configured (Cloudflare Turnstile)
- [ ] SQL injection prevention verified
- [ ] XSS prevention verified
- [ ] Input sanitization implemented

### Configuration
- [ ] `.env.local` created with production values
- [ ] Database connection string updated
- [ ] Redis connection configured
- [ ] Analytics IDs added (GA4, Yandex Metrika)
- [ ] SMS API configured
- [ ] Email API configured
- [ ] CRM API configured
- [ ] External URLs updated (Yandex Reviews)
- [ ] Support contact info updated

## Deployment

### Database
- [ ] PostgreSQL 14+ installed
- [ ] Database created: `warranty_db`
- [ ] Schema applied: `psql -d warranty_db -f database/schema.sql`
- [ ] Indexes created and verified
- [ ] Sample data inserted (for testing)
- [ ] Backup strategy configured
- [ ] Connection pooling configured
- [ ] Database user permissions set correctly

### Application
- [ ] Dependencies installed: `npm ci --only=production`
- [ ] Build completed successfully: `npm run build`
- [ ] No build errors or warnings
- [ ] Build size acceptable (check `.next` folder)
- [ ] Static assets optimized

### Infrastructure
- [ ] Server/VM provisioned (or Vercel project created)
- [ ] Node.js 18+ installed
- [ ] Redis installed and running
- [ ] PM2 installed (if using VPS): `npm i -g pm2`
- [ ] Nginx configured (if using VPS)
- [ ] SSL certificate installed
- [ ] Firewall configured (allow 80, 443)
- [ ] Domain DNS configured
- [ ] CDN configured (optional but recommended)

### Monitoring
- [ ] Error tracking configured (Sentry/LogRocket)
- [ ] Performance monitoring configured
- [ ] Uptime monitoring configured (UptimeRobot/Pingdom)
- [ ] Log aggregation configured
- [ ] Alerting rules configured
- [ ] Dashboard created for key metrics

### External Services
- [ ] SMS provider account active
- [ ] Email provider account active
- [ ] CRM integration tested end-to-end
- [ ] Analytics tracking verified
- [ ] CAPTCHA provider active
- [ ] All webhooks configured

## Post-Deployment

### Verification
- [ ] Application loads at production URL
- [ ] HTTPS working correctly (no mixed content)
- [ ] All pages accessible
- [ ] Form submits successfully
- [ ] API endpoints responding correctly
- [ ] Database connections working
- [ ] Redis connections working
- [ ] Analytics events firing
- [ ] Error pages display correctly

### Performance
- [ ] Page load time < 3 seconds
- [ ] API response time < 500ms
- [ ] Lighthouse score > 90 (all categories)
- [ ] No console errors in browser
- [ ] Mobile performance acceptable

### Functionality Testing
- [ ] Test with real contract ID
- [ ] Test with real phone number
- [ ] Verify warranty activation email sent
- [ ] Verify SMS notification sent (if enabled)
- [ ] Verify CRM updated with data
- [ ] Test discount reservation
- [ ] Verify Yandex Reviews links work
- [ ] Test on multiple devices
- [ ] Test in multiple browsers

### Security Verification
- [ ] HTTPS only (no HTTP access)
- [ ] Security headers present (check securityheaders.com)
- [ ] CORS working correctly
- [ ] Rate limiting active and blocking excess requests
- [ ] CAPTCHA blocking bots
- [ ] No sensitive data exposed in responses
- [ ] Audit logs being created

### Documentation
- [ ] API documentation accessible
- [ ] Internal wiki updated
- [ ] Deployment notes documented
- [ ] Known issues documented
- [ ] Support team briefed
- [ ] Runbook created for common issues

## Rollback Plan

### If Issues Occur
- [ ] Rollback procedure documented
- [ ] Previous version tagged in git
- [ ] Database rollback script ready (if schema changed)
- [ ] Quick rollback command prepared
- [ ] Team notified of rollback procedure

### Rollback Steps
1. Identify issue severity
2. Notify team via Slack/email
3. Execute rollback:
   - Vercel: Dashboard → Redeploy previous
   - VPS: `git checkout <tag> && npm run build && pm2 restart`
4. Verify rollback successful
5. Post-mortem within 24 hours

## Monitoring Checklist (First 24 Hours)

### Hour 1
- [ ] No 5xx errors
- [ ] Response times normal
- [ ] Database connections stable
- [ ] No memory leaks

### Hour 6
- [ ] Form submissions working
- [ ] Analytics data coming in
- [ ] No user complaints
- [ ] Error rate < 1%

### Hour 24
- [ ] All systems stable
- [ ] Performance metrics acceptable
- [ ] No critical bugs reported
- [ ] User feedback positive

## Communication

### Before Deployment
- [ ] Stakeholders notified of deployment window
- [ ] Support team prepared for questions
- [ ] Maintenance window scheduled (if needed)
- [ ] Status page updated

### After Deployment
- [ ] Stakeholders notified of success
- [ ] Documentation shared with team
- [ ] Post-deployment meeting scheduled
- [ ] Lessons learned documented

## Success Criteria

### Must Have (Blocker if missing)
- ✅ Application accessible via HTTPS
- ✅ Form submits successfully
- ✅ Data persists to database
- ✅ No critical security issues
- ✅ Error tracking operational

### Should Have (Fix within 48 hours)
- ✅ SMS/Email notifications working
- ✅ CRM integration functional
- ✅ Analytics tracking correctly
- ✅ Performance metrics acceptable

### Nice to Have (Fix within 1 week)
- ✅ Advanced monitoring dashboards
- ✅ Automated alerts configured
- ✅ Load testing completed
- ✅ A/B testing framework ready

## Sign-Off

**Deployed by:** _________________  
**Date:** _________________  
**Version:** _________________  
**Environment:** _________________  

**Reviewed by:** _________________  
**Approved by:** _________________  

**Notes:**
________________________________________________________________
________________________________________________________________
________________________________________________________________

## Emergency Contacts

**On-Call Developer:** +7 XXX XXX-XX-XX  
**DevOps Lead:** +7 XXX XXX-XX-XX  
**CTO/Tech Lead:** +7 XXX XXX-XX-XX  
**Hosting Provider Support:** +7 XXX XXX-XX-XX  

---

**Remember:** It's better to delay deployment than to rush and cause issues!

**Last Updated:** 2025-10-30
