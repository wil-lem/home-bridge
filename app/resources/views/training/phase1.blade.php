@extends('layout')

@section('title', 'Phase 1: API Understanding')

@section('content')
<div class="card">
    <h2>Phase 1: API Understanding (Foundation)</h2>
    <p><strong>Duration:</strong> Week 1-2</p>
    <p><strong>Objective:</strong> Teach the model the available endpoints and their functions.</p>
</div>

<div class="card">
    <h2>Training Examples</h2>
    
    <h3>💡 Lights</h3>
    <pre><code>@verbatim{
  "instruction": "Turn on the kitchen light",
  "output": "POST /api/lights/kitchen/on"
}

{
  "instruction": "Turn off the bedroom light",
  "output": "POST /api/lights/bedroom/off"
}

{
  "instruction": "Set the living room light to 50% brightness",
  "output": "POST /api/lights/living_room/brightness\nBody: {\"brightness\": 128}"
}

{
  "instruction": "Dim the bedroom light to 25%",
  "output": "POST /api/lights/bedroom/brightness\nBody: {\"brightness\": 64}"
}@endverbatim</code></pre>

    <h3>🔌 Switches</h3>
    <pre><code>@verbatim{
  "instruction": "Turn on the coffee maker",
  "output": "POST /api/switches/coffee_maker/on"
}

{
  "instruction": "Turn off the fan",
  "output": "POST /api/switches/fan/off"
}@endverbatim</code></pre>

    <h3>📊 Sensors</h3>
    <pre><code>@verbatim{
  "instruction": "What's the temperature in the living room?",
  "output": "GET /api/sensors/temperature"
}

{
  "instruction": "Check the humidity in the bathroom",
  "output": "GET /api/sensors/humidity"
}

{
  "instruction": "What's the battery level of my sensors?",
  "output": "GET /api/sensors/battery"
}@endverbatim</code></pre>

    <h3>🌡️ Climate</h3>
    <pre><code>@verbatim{
  "instruction": "Set the thermostat to 22 degrees",
  "output": "POST /api/climate/living_room/temperature\nBody: {\"temperature\": 22}"
}@endverbatim</code></pre>
</div>

<div class="card">
    <h2>Dataset Generation Script</h2>
    <p>Generate training data from your actual Home Assistant setup:</p>
    <pre><code>@verbatim
import requests
import json

def generate_phase1_training_data():
    # Fetch your actual entities
    entities = requests.get('http://localhost:8383/api/entities').json()
    
    training_data = []
    
    # Generate light control examples
    for entity in entities:
        if entity['type'] == 'light':
            name = entity['attributes'].get('friendly_name', entity['entity_id'])
            entity_id = entity['entity_id'].split('.')[1]
            
            training_data.extend([
                {
                    "instruction": f"Turn on the {name}",
                    "output": f"POST /api/lights/{entity_id}/on"
                },
                {
                    "instruction": f"Turn off the {name}",
                    "output": f"POST /api/lights/{entity_id}/off"
                },
                {
                    "instruction": f"Set {name} to 50%",
                    "output": f"POST /api/lights/{entity_id}/brightness\nBody: {{\"brightness\": 128}}"
                }
            ])
    
    # Save to file
    with open('phase1_training.json', 'w') as f:
        json.dump(training_data, f, indent=2)
    
    print(f"Generated {len(training_data)} training examples")

# Run the function
generate_phase1_training_data()
@endverbatim</code></pre>
</div>

<div class="card">
    <h2>Validation Criteria</h2>
    <ul style="margin-left: 20px; line-height: 1.8;">
        <li>Model correctly maps 95%+ of simple commands to API endpoints</li>
        <li>Understands difference between lights, switches, and sensors</li>
        <li>Correctly extracts entity IDs from natural language</li>
        <li>Generates proper JSON bodies for brightness/temperature commands</li>
    </ul>
</div>

<div class="card">
    <h2>Next Steps</h2>
    <p>Once you achieve >95% accuracy on Phase 1:</p>
    <ul style="margin-left: 20px; line-height: 1.8;">
        <li>Proceed to <a href="/training/phase2">Phase 2: Context Awareness</a></li>
        <li>Add more entity types (covers, media players, etc.)</li>
        <li>Create variations of successful patterns</li>
    </ul>
</div>

<div style="display: flex; justify-content: space-between; margin-top: 20px;">
    <a href="/training/overview" class="btn btn-primary">← Back to Overview</a>
    <a href="/training/phase2" class="btn btn-primary">Phase 2 →</a>
</div>
@endsection
