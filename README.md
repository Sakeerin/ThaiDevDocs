# Thai Developer Docs (ThaiDevDocs)

แพลตฟอร์มเอกสารสำหรับนักพัฒนาไทย - เว็บเอกสารภาษาไทยสำหรับนักพัฒนาซอฟต์แวร์

A comprehensive documentation platform for Thai developers, inspired by MDN Web Docs.

## Features

- 📚 **Documentation Platform** - Comprehensive Thai language documentation for developers
- 🔍 **Full-text Search** - Powered by Meilisearch with Thai language support
- 💻 **Interactive Code Playground** - Live code examples with HTML/CSS/JS
- 📝 **Markdown Editor** - Rich editor with live preview for content creators
- 👥 **Community Contributions** - Edit suggestions and contribution points
- 🎯 **Learning Paths** - Structured learning tracks with progress tracking
- 🌙 **Dark Mode** - System-aware theme support
- 📱 **Responsive Design** - Mobile-first approach

## Tech Stack

### Backend
- **Framework**: Laravel 11
- **Database**: MySQL 8.0
- **Cache**: Redis
- **Search**: Meilisearch
- **Queue**: Laravel Horizon
- **Auth**: Laravel Sanctum

### Frontend
- **Framework**: Nuxt 3
- **Styling**: Tailwind CSS
- **UI Components**: Headless UI
- **Icons**: Heroicons

### Admin Panel
- **Framework**: Vue 3 + Vite
- **Editor**: Tiptap (ProseMirror)
- **Charts**: ApexCharts

## Project Structure

```
ThaiDevDocs/
├── backend/           # Laravel API
│   ├── app/
│   ├── config/
│   ├── database/
│   ├── routes/
│   └── ...
├── frontend/          # Nuxt 3 frontend
│   ├── assets/
│   ├── components/
│   ├── pages/
│   ├── stores/
│   └── ...
├── admin/             # Vue 3 admin panel
│   ├── src/
│   └── ...
├── docker/            # Docker configurations
└── .github/           # GitHub Actions CI/CD
```

## Quick Start

### Prerequisites

- Docker & Docker Compose
- Node.js 20+
- PHP 8.3+
- Composer 2+

### Development Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-org/thaidevdocs.git
   cd thaidevdocs
   ```

2. **Start backend services**
   ```bash
   cd backend
   docker-compose up -d
   composer install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate
   php artisan db:seed
   php artisan serve
   ```

3. **Start frontend**
   ```bash
   cd frontend
   npm install
   npm run dev
   ```

4. **Start admin panel**
   ```bash
   cd admin
   npm install
   npm run dev
   ```

### Access URLs

| Service | URL |
|---------|-----|
| Frontend | http://localhost:3000 |
| API | http://localhost:8000 |
| Admin | http://localhost:5173 |
| Meilisearch | http://localhost:7700 |

## API Documentation

### Authentication

```bash
# Register
POST /api/v1/auth/register
{
  "name": "User Name",
  "email": "user@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}

# Login
POST /api/v1/auth/login
{
  "email": "user@example.com",
  "password": "password123"
}
```

### Content API

```bash
# Get categories
GET /api/v1/categories

# Get articles
GET /api/v1/articles?category_id=1&difficulty=beginner

# Search
GET /api/v1/search?q=javascript
```

See `backend/routes/api.php` and `DEVELOPMENT-CHECKLIST.md` for the full API surface.

## Environment Variables

### Backend (.env)

```env
APP_NAME="Thai Developer Docs"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=thaidevdocs
DB_USERNAME=root
DB_PASSWORD=

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MEILISEARCH_HOST=http://127.0.0.1:7700
MEILISEARCH_KEY=masterKey

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

### Frontend (.env)

```env
NUXT_PUBLIC_API_BASE=http://localhost:8000/api/v1
```

## Testing

### Backend Tests

```bash
cd backend
php artisan test
php artisan test --coverage
```

### Frontend Tests

```bash
cd frontend
npm run test
```

## Deployment

See [DEPLOYMENT.md](./DEPLOYMENT.md) for detailed production deployment instructions.

### Quick Deploy

```bash
# Build and start all services
docker-compose -f docker-compose.prod.yml up -d --build

# Run migrations
docker-compose -f docker-compose.prod.yml exec backend php artisan migrate --force
```

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Contribution Guidelines

- Follow PSR-12 for PHP code
- Use ESLint/Prettier for TypeScript/Vue
- Write tests for new features
- Update documentation as needed

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments

- Inspired by [MDN Web Docs](https://developer.mozilla.org/)
- Thai developer community
- All contributors

---

Built with ❤️ for Thai developers
