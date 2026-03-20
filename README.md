# Home Assistant Bridge API

**AI-Friendly Home Automation Control**

A Laravel-based API bridge for Home Assistant, designed for both human developers and AI-driven automation (LLaMA 3 integration).

## Features

- 🏠 **Smart Home Control** - Lights, switches, sensors, climate devices
- 🤖 **AI-Optimized** - Clean, semantic endpoints designed for LLM training
- 📊 **Sensor Monitoring** - Temperature, humidity, battery levels
- 🌡️ **Climate Control** - Thermostat and HVAC management
- 🔍 **Area-Based Queries** - Group devices by room/area
- � **Batch Operations** - Control all devices in a room at once
- 🌐 **Web Interface** - Interactive API testing and training guide viewer
- �📝 **Well Documented** - Comprehensive API docs and training guides

## Quick Start

### 1. Configure Environment

Copy and configure your environment file:
```bash
cp app/.env.example app/.env
```

Add your Home Assistant credentials:
```env
HOME_ASSISTANT_URL=http://homeassistant.local:8123
HOME_ASSISTANT_API_KEY=your_long_lived_access_token
```

### 2. Start Docker Containers

```bash
docker-compose up -d
```

### 3. Install Dependencies and Generate Application Key

```bash
# Install PHP dependencies
docker exec -it home_bridge_php composer install

# Generate application key
docker exec -it home_bridge_php php artisan key:generate

# Create required directories
docker exec -it home_bridge_php mkdir -p storage/framework/{sessions,views,cache}
docker exec -it home_bridge_php chmod -R 775 storage bootstrap/cache
```

### 4. Access the API

The API will be available at: **http://localhost:8383/api**

Test it:
```bash
curl http://localhost:8383/api/status
```

### 5. Use the Web Interface

Access the interactive web interface at: **http://localhost:8383**

Features:
- **API Testing** - Test all endpoints directly from your browser
- **Area Control** - Batch control all lights in a room
- **Training Guide** - Browse the AI training strategy phases
- **Live Status** - View Home Assistant connection status
- **Request/Response Viewer** - See actual API calls and responses
- **Copy to Clipboard** - Easy copying of requests and responses

## Documentation

📖 **[API Documentation](API_DOCUMENTATION.md)** - Complete endpoint reference

🤖 **[AI Training Strategy](AI_TRAINING_STRATEGY.md)** - Guide for training LLaMA 3

## API Overview

### Quick Examples

**Get all lights:**
```bash
curl http://localhost:8383/api/lights
```

**Turn on kitchen light:**
```bash
curl -X POST http://localhost:8383/api/lights/kitchen/on
```

**Set brightness to 50%:**
```bash
curl -X POST http://localhost:8383/api/lights/kitchen/brightness \
  -H "Content-Type: application/json" \
  -d '{"brightness": 128}'
```

**Get temperature sensors:**
```bash
curl http://localhost:8383/api/sensors/temperature
```

**Check battery levels:**
```bash
curl http://localhost:8383/api/sensors/battery
```

### Available Endpoints

- **System:** `/api/status`
- **Discovery:** `/api/entities`, `/api/areas`, `/api/areas/{area}/entities`
- **Lights:** `/api/lights`, `/api/lights/{id}/on|off|brightness`
- **Switches:** `/api/switches`, `/api/switches/{id}/on|off`
- **Sensors:** `/api/sensors`, `/api/sensors/battery|temperature|humidity`
- **Climate:** `/api/climate`, `/api/climate/{id}/temperature`

### Area-Based Batch Operations

Control multiple devices in a room at once:

```bash
# Get all entities in the kitchen
curl http://localhost:8383/api/areas/kitchen/entities

# Turn on all kitchen lights (filter for type==='light', then iterate)
curl -X POST http://localhost:8383/api/lights/kitchen_ceiling/on
curl -X POST http://localhost:8383/api/lights/kitchen_counter/on

# Set brightness for all lights in bedroom
curl -X POST http://localhost:8383/api/lights/bedroom_ceiling/brightness \
  -H "Content-Type: application/json" -d '{"brightness": 64}'
curl -X POST http://localhost:8383/api/lights/bedroom_lamp/brightness \
  -H "Content-Type: application/json" -d '{"brightness": 32}'
```

**Note:** Batch operations are implemented client-side by:
1. Fetching entities for an area
2. Filtering by device type
3. Iterating through each device

## System Details

- **Framework:** Laravel 13
- **PHP Version:** 8.3
- **Server:** Nginx
- **Port:** 8383

## Project Structure

```
home-bridge/
├── app/                          # Laravel application
│   ├── app/
│   │   ├── Http/Controllers/     # API controllers
│   │   │   ├── ApiController.php
│   │   │   ├── AreasController.php
│   │   │   ├── ClimateController.php
│   │   │   ├── HomeAssistantController.php
│   │   │   ├── LightsController.php
│   │   │   ├── SensorsController.php
│   │   │   └── SwitchesController.php
│   │   └── Services/
│   │       └── HomeAssistantService.php
│   ├── config/
│   │   └── homeassistant.php     # HA configuration
│   ├── routes/
│   │   ├── api.php               # API routes
│   │   └── web.php               # Web interface routes
│   └── resources/
│       └── views/                # Web interface
│           ├── layout.blade.php
│           ├── home.blade.php    # API testing UI
│           └── training/         # Training guide pages
├── docker/                       # Docker configuration
│   ├── Dockerfile
│   ├── nginx.conf
│   └── mysql.cnf
├── docker-compose.yml
├── API_DOCUMENTATION.md          # API reference
├── AI_TRAINING_STRATEGY.md       # AI training guide
└── README.md                     # This file
```

## Container Management

### Container Names
- **PHP:** `home_bridge_php`
- **Nginx:** `home_bridge_nginx`
- **Redis:** `home_bridge_redis`

### Useful Commands

**View logs:**
```bash
docker logs home_bridge_nginx -f
```

**Enter PHP container:**
```bash
docker exec -it home_bridge_php bash
```

**Restart services:**
```bash
docker-compose restart
```

**Run artisan commands:**
```bash
make artisan
# or
docker exec -it home_bridge_php php artisan
```

## AI Integration

This API is specifically designed for training and running local AI models (like LLaMA 3) to control your smart home through natural language.

### Key Design Principles

1. **Semantic Clarity** - Endpoints use natural language (`/on`, `/off`)
2. **Consistent Patterns** - Similar actions across device types
3. **Simplified Responses** - Only relevant data, no noise
4. **Type Classification** - Automatic entity type extraction
5. **Clear Feedback** - All actions return success status and what changed

### Training Your AI

See [AI_TRAINING_STRATEGY.md](AI_TRAINING_STRATEGY.md) for:
- Dataset creation from your actual devices
- Fine-tuning LLaMA 3 with LoRA
- Progressive training phases
- Testing and validation strategies
- Deployment recommendations

## Development

### Adding New Endpoints

1. **Create/Update Service** - Add method to `HomeAssistantService.php`
2. **Create/Update Controller** - Add endpoint handler
3. **Register Route** - Add to `routes/api.php`
4. **Document** - Update `API_DOCUMENTATION.md`

### Example: Adding Media Player Control

```php
// app/Services/HomeAssistantService.php
public function playMedia(string $entityId): bool
{
    return $this->callService('media_player', 'media_play', ['entity_id' => $entityId]);
}

// app/Http/Controllers/MediaController.php
public function play(string $entityId): JsonResponse
{
    $success = $this->homeAssistant->playMedia("media_player.{$entityId}");
    return response()->json(['success' => $success]);
}

// routes/api.php
Route::post('/media/{entityId}/play', [MediaController::class, 'play']);
```

## Troubleshooting

### API Returns "Home Assistant configuration missing"
- Check `.env` has `HOME_ASSISTANT_URL` and `HOME_ASSISTANT_API_KEY`
- Verify Home Assistant is accessible from Docker container

### Connection Errors
- Ensure Home Assistant URL is accessible from inside Docker
- Try using IP address instead of hostname
- Check firewall settings

### Nginx Container Restarting
- Check logs: `docker logs home_bridge_nginx`
- Verify nginx.conf syntax
- Ensure PHP container name matches in nginx config

## Contributing

This is a personal project, but suggestions are welcome!

1. Document your use case
2. Propose endpoint design
3. Consider AI training implications
4. Update documentation

## License

Personal project - use at your own risk.

## Acknowledgments

- **Home Assistant** - The amazing open-source home automation platform
- **Laravel** - Elegant PHP framework
- **LLaMA 3** - Meta's language model for AI integration


Path to a mounted key file:
```
SSH_PRIVATE_KEY_PATH=/run/secrets/deploy_key
```

Guidelines:
- Key must be readable by the PHP process.
- OpenSSH or PEM formats supported (phpseclib 3).
- No commands are executed; only connection + auth attempted.
- If auth fails but host is reachable, status shows reachable with auth failure message.
