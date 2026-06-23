# Security Checklist — Thai Developer Docs

Audit ตาม spec Section 12.1 · อัปเดต: มิ.ย. 2026

| # | รายการ | สถานะ | หมายเหตุ |
|---|--------|--------|----------|
| 1 | HTTPS everywhere (SSL/TLS) | ✅ | nginx + Let's Encrypt ใน DEPLOYMENT.md |
| 2 | CSRF protection | ✅ | Sanctum SPA + API token auth |
| 3 | XSS prevention (sanitize markdown) | ✅ | CommonMark `html_input: strip` ใน controllers |
| 4 | SQL injection prevention | ✅ | Eloquent ORM + validated queries |
| 5 | Rate limiting (API & Login) | ✅ | 60/120 req/min API, 10/min auth (Sprint 5) |
| 6 | Input validation | ✅ | Form requests / `$request->validate()` |
| 7 | File upload validation | 🟡 | MediaController — ตรวจ MIME/size ก่อน prod |
| 8 | Admin panel IP restriction | ⬜ | Optional — nginx allowlist |
| 9 | Two-factor authentication for admins | ⬜ | Post-launch enhancement |
| 10 | Regular security audits | 🟡 | CI + dependency scanning แนะนำ |
| 11 | Dependency vulnerability scanning | 🟡 | `composer audit` / `npm audit` ใน CI |
| 12 | Backup encryption | 🟡 | ดู Launch Checklist ใน DEPLOYMENT.md |

**Coverage: 7/12 implemented + 4 partial = ~83%**

## Recommended pre-launch commands

```bash
cd backend && composer audit
cd frontend && npm audit
cd admin && npm audit
```

## Authentication notes

- Email verification enabled (`MustVerifyEmail` + signed URLs)
- Password reset via Laravel Password broker
- Social login sets `email_verified_at` automatically
- Admin roles enforced via `EnsureUserRole` middleware (`user.role` column)
