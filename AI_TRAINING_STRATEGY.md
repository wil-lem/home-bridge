# AI Training Strategy for Home Assistant Control

## Overview
This document outlines a training strategy for LLaMA 3 (or similar LLM) to control Home Assistant through the bridge API. The goal is to create an AI assistant that can understand natural language commands and translate them into API calls.

---

## Training Approach

### Phase 1: API Understanding (Foundation)

**Objective:** Teach the model the available endpoints and their functions.

#### Dataset Structure
Create training examples mapping natural language to API calls:

```json
{
  "instruction": "Turn on the kitchen light",
  "input": "",
  "output": "POST /api/lights/kitchen/on"
}
```

#### Core Training Examples

**Lights:**
```json
[
  {
    "instruction": "Turn on the bedroom light",
    "output": "POST /api/lights/bedroom/on"
  },
  {
    "instruction": "Turn off all lights in the kitchen",
    "output": "POST /api/lights/kitchen/off"
  },
  {
    "instruction": "Set the living room light to 50% brightness",
    "output": "POST /api/lights/living_room/brightness\nBody: {\"brightness\": 128}"
  },
  {
    "instruction": "Dim the bedroom light to 25%",
    "output": "POST /api/lights/bedroom/brightness\nBody: {\"brightness\": 64}"
  },
  {
    "instruction": "Make the kitchen light brighter",
    "output": "First: GET /api/lights/kitchen\nThen: POST /api/lights/kitchen/brightness with increased value"
  }
]
```

**Switches:**
```json
[
  {
    "instruction": "Turn on the coffee maker",
    "output": "POST /api/switches/coffee_maker/on"
  },
  {
    "instruction": "Turn off the fan",
    "output": "POST /api/switches/fan/off"
  }
]
```

**Sensors:**
```json
[
  {
    "instruction": "What's the temperature in the living room?",
    "output": "GET /api/sensors/temperature"
  },
  {
    "instruction": "Check the humidity in the bathroom",
    "output": "GET /api/sensors/humidity"
  },
  {
    "instruction": "What's the battery level of my sensors?",
    "output": "GET /api/sensors/battery"
  }
]
```

**Climate:**
```json
[
  {
    "instruction": "Set the thermostat to 22 degrees",
    "output": "POST /api/climate/living_room/temperature\nBody: {\"temperature\": 22}"
  },
  {
    "instruction": "Make it warmer",
    "output": "First: GET /api/climate\nThen: POST /api/climate/{id}/temperature with +1 degree"
  }
]
```

**Areas & Batch Operations:**
```json
[
  {
    "instruction": "Show me all the rooms",
    "output": "GET /api/areas"
  },
  {
    "instruction": "What devices are in the kitchen?",
    "output": "GET /api/areas/kitchen/entities"
  },
  {
    "instruction": "Turn on all lights in the bedroom",
    "output": "First: GET /api/areas/bedroom/entities\nThen: Filter for type==='light'\nThen: POST /api/lights/{each_light_id}/on"
  },
  {
    "instruction": "Set all kitchen lights to 50%",
    "output": "First: GET /api/areas/kitchen/entities\nFilter for lights\nThen: POST /api/lights/{each_light_id}/brightness with {\"brightness\": 128}"
  },
  {
    "instruction": "Turn off everything in the living room",
    "output": "First: GET /api/areas/living/entities\nThen: Turn off each light and switch found"
  }
]
```

---

### Phase 2: Context Awareness (Intermediate)

**Objective:** Teach the model to handle ambiguity and maintain context.

#### Training Examples with Context

**Room/Area Context:**
```json
[
  {
    "conversation": [
      {"user": "I'm in the kitchen", "ai": "Noted. You're in the kitchen."},
      {"user": "Turn on the light", "ai": "POST /api/lights/kitchen/on"}
    ]
  },
  {
    "conversation": [
      {"user": "What's the temperature?", "ai": "GET /api/sensors/temperature"},
      {"user": "In which room?", "ai": "Could you specify which room you'd like to check?"}
    ]
  }
]
```

**Brightness Interpretation:**
```json
[
  {
    "instruction": "Turn on the light very dim",
    "output": "POST /api/lights/{id}/on\nBody: {\"brightness\": 32}"
  },
  {
    "instruction": "Turn on the light at medium brightness",
    "output": "POST /api/lights/{id}/on\nBody: {\"brightness\": 128}"
  },
  {
    "instruction": "Turn on the light bright",
    "output": "POST /api/lights/{id}/on\nBody: {\"brightness\": 255}"
  }
]
```

**Brightness Mapping Guide:**
- "very dim" / "barely on" → 20-50 (8-20%)
- "dim" / "low" → 50-100 (20-40%)
- "medium" / "half" → 100-150 (40-60%)
- "bright" → 200-255 (80-100%)
- "full" / "maximum" → 255 (100%)

---

### Phase 3: Multi-Step Reasoning (Advanced)

**Objective:** Enable complex scenarios requiring multiple API calls.

#### Scenario-Based Training

**Morning Routine:**
```json
{
  "instruction": "Start my morning routine",
  "reasoning": "Morning routine typically involves: checking weather, turning on lights, starting coffee",
  "output": [
    "GET /api/sensors/temperature",
    "GET /api/areas/kitchen/entities",
    "POST /api/lights/kitchen_ceiling/on with {\"brightness\": 200}",
    "POST /api/lights/kitchen_counter/on with {\"brightness\": 128}",
    "POST /api/switches/coffee_maker/on"
  ]
}
```

**Area-Wide Control:**
```json
{
  "instruction": "Turn on all bedroom lights",
  "reasoning": "Get bedroom area entities, filter for lights, turn each on",
  "output": [
    "GET /api/areas/bedroom/entities",
    "Filter response for entities where type === 'light'",
    "For each light: POST /api/lights/{id}/on"
  ]
}
```

**Batch Brightness Control:**
```json
{
  "instruction": "Dim all living room lights to 25%",
  "reasoning": "Query area, identify lights, set brightness for each",
  "output": [
    "GET /api/areas/living/entities",
    "Filter for type === 'light'",
    "For each: POST /api/lights/{id}/brightness with {\"brightness\": 64}"
  ]
}
```

**Energy Saving:**
```json
{
  "instruction": "I'm leaving the house, save energy",
  "reasoning": "Turn off lights, adjust thermostat, turn off non-essential switches",
  "output": [
    "GET /api/lights (to find all on lights)",
    "POST /api/lights/{each_on_light}/off",
    "POST /api/climate/living_room/temperature with {\"temperature\": 19}"
  ]
}
```

**Comfort Optimization:**
```json
{
  "instruction": "Make the bedroom comfortable for sleep",
  "reasoning": "Dim lights, adjust temperature, check humidity",
  "output": [
    "POST /api/lights/bedroom/brightness with {\"brightness\": 10}",
    "POST /api/climate/bedroom/temperature with {\"temperature\": 18}",
    "GET /api/sensors/humidity"
  ]
}
```

---

### Phase 4: State-Aware Decisions (Expert)

**Objective:** Make intelligent decisions based on current state.

#### Conditional Logic Examples

```json
{
  "instruction": "Turn on the light if it's off",
  "logic": [
    "1. GET /api/lights/{id} to check current state",
    "2. If state === 'off': POST /api/lights/{id}/on",
    "3. Else: respond 'The light is already on'"
  ]
}
```

```json
{
  "instruction": "Make sure all lights are off",
  "logic": [
    "1. GET /api/lights",
    "2. Filter lights where state === 'on'",
    "3. For each: POST /api/lights/{id}/off",
    "4. Respond with count of lights turned off"
  ]
}
```

```json
{
  "instruction": "Alert me if any battery is low",
  "logic": [
    "1. GET /api/sensors/battery",
    "2. Filter devices where battery_level < 20",
    "3. Return list of devices needing battery replacement"
  ]
}
```

---

## Training Dataset Generation

### Automated Dataset Creation

**Script Concept:**
```python
# Generate training data from your actual Home Assistant setup
import requests

def generate_training_data():
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
    
    return training_data
```

### Synthetic Data Augmentation

Create variations of commands:
- "Turn on the kitchen light"
- "Switch on the kitchen light"
- "Activate the kitchen light"
- "Light up the kitchen"
- "I need light in the kitchen"

---

## Fine-Tuning Recommendations

### LLaMA 3 Fine-Tuning

**Recommended Approach: LoRA (Low-Rank Adaptation)**

**Why LoRA:**
- Efficient: Only trains small adapter layers
- Fast: Trains in hours instead of days
- Preserves: Keeps base model knowledge
- Flexible: Easy to switch between tasks

**Configuration:**
```yaml
model: meta-llama/llama-3-8b
lora_rank: 16
lora_alpha: 32
lora_dropout: 0.05
learning_rate: 3e-4
batch_size: 4
epochs: 3
max_seq_length: 512
```

### Training Steps

1. **Data Preparation**
   - Format: Alpaca/Instruction format
   - Size: Minimum 1,000 examples per entity type
   - Quality: Manual review of edge cases

2. **Base Training**
   - Start with simple commands
   - Single-step actions first
   - Validate accuracy >95% before proceeding

3. **Context Training**
   - Add conversation history
   - Multi-turn dialogues
   - Ambiguity resolution

4. **Advanced Training**
   - Multi-step scenarios
   - State-dependent logic
   - Error recovery

### Validation Metrics

**Accuracy Goals:**
- Single command: >98%
- Contextual command: >95%
- Multi-step scenario: >90%
- Error handling: >85%

---

## System Prompt Template

```
You are a home automation assistant with access to a Home Assistant API. Your role is to help users control their smart home devices through natural language commands.

Available API endpoints:
- GET /api/status - Check API health and Home Assistant connectivity
- GET /api/entities - Get all entities with type classification
- GET /api/areas - List all configured areas/rooms
- GET /api/areas/{area}/entities - Get all entities in a specific area
- GET/POST /api/lights/* - Control lights (on/off/brightness)
- GET/POST /api/switches/* - Control switches (on/off)
- GET /api/sensors/* - Read sensor data (temperature, humidity, battery)
- GET/POST /api/climate/* - Control climate devices (thermostats)

Guidelines:
1. Always confirm which device the user wants to control if ambiguous
2. For brightness: 0-255 scale (low=64, medium=128, high=200, full=255)
3. Check current state before making changes when appropriate
4. Provide clear feedback about actions taken
5. Handle errors gracefully and suggest alternatives
6. For batch operations ("all lights in..."), use the areas endpoint to get entities first
7. Filter entities by type before performing batch operations

When asked to control a device:
1. Identify the entity type (light, switch, sensor, climate)
2. Extract the device identifier
3. Determine the action (on/off/set value)
4. Construct the appropriate API call
5. Explain what you're doing

Example:
User: "Turn on the kitchen light"
Response: "I'll turn on the kitchen light for you."
API Call: POST /api/lights/kitchen/on
```

---

## Implementation Tools

### Recommended Stack

**Training:**
- Framework: Hugging Face Transformers + PEFT (LoRA)
- Dataset: Alpaca format with custom examples
- GPU: Minimum 24GB VRAM (RTX 3090/4090 or A100)

**Inference:**
- Server: Ollama or vLLM
- API: FastAPI or Flask wrapper
- Context: Maintain conversation history

**Integration:**
```python
# Example inference wrapper
import requests
from ollama import Client

class SmartHomeAI:
    def __init__(self):
        self.ollama = Client()
        self.api_base = "http://localhost:8383/api"
        
    def process_command(self, user_input):
        # Get AI response
        response = self.ollama.generate(
            model='llama3-homeassistant',
            prompt=user_input
        )
        
        # Extract API calls from response
        api_calls = self.parse_api_calls(response)
        
        # Execute API calls
        results = []
        for call in api_calls:
            result = requests.request(
                call['method'],
                f"{self.api_base}{call['endpoint']}",
                json=call.get('body')
            )
            results.append(result.json())
        
        return results
```

---

## Testing Strategy

### Test Categories

**1. Basic Commands (Unit Tests)**
```json
[
  {"input": "Turn on bedroom light", "expect": "POST /api/lights/bedroom/on"},
  {"input": "What's the temperature?", "expect": "GET /api/sensors/temperature"}
]
```

**2. Contextual Commands (Integration Tests)**
```json
[
  {
    "context": "User previously mentioned 'kitchen'",
    "input": "Turn on the light",
    "expect": "POST /api/lights/kitchen/on"
  }
]
```

**3. Edge Cases (Stress Tests)**
```json
[
  {"input": "Turn on all the lights", "expect": "Multiple POST calls"},
  {"input": "What's wrong with my sensors?", "expect": "GET /api/sensors/battery"},
  {"input": "Make it darker", "expect": "Decrease brightness"}
]
```

### Success Criteria

- ✓ Correctly interprets 95%+ of single commands
- ✓ Maintains context across conversations
- ✓ Handles unknown entities gracefully
- ✓ Provides helpful error messages
- ✓ Confirms ambiguous requests

---

## Progressive Training Schedule

### Week 1-2: Foundation
- Train on basic on/off commands
- Single entity type at a time
- Validate >95% accuracy

### Week 3-4: Expansion
- Add brightness/temperature controls
- Introduce second entity type
- Cross-entity commands

### Week 5-6: Context
- Conversation history
- Room awareness
- Implicit references

### Week 7-8: Advanced
- Multi-step scenarios
- State-based decisions
- Error recovery

### Week 9-10: Polish
- Edge case handling
- Response quality
- User experience refinement

---

## Continuous Improvement

### Feedback Loop
1. Log all user commands
2. Track successful vs. failed interpretations
3. Identify common failure patterns
4. Generate training data for failures
5. Retrain model monthly

### Metrics to Track
- Command interpretation accuracy
- API call success rate
- Average commands per conversation
- User satisfaction scores
- Response time

---

## Safety Considerations

### Guardrails
- Confirm destructive actions ("turn off all")
- Rate limiting on API calls
- Validation of temperature/brightness ranges
- Prevent conflicting simultaneous commands

### Privacy
- Don't log sensitive information
- Anonymize training data
- Local inference when possible
- Secure API token storage

---

## Next Steps

1. **Generate Initial Dataset**
   - Use your actual Home Assistant entities
   - Create 1,000+ training examples
   - Include variations and edge cases

2. **Set Up Training Environment**
   - GPU server or cloud instance
   - Install transformers, PEFT, datasets libraries
   - Download LLaMA 3 base model

3. **Run Baseline Test**
   - Test untrained model on sample commands
   - Document baseline accuracy
   - Identify gaps

4. **Fine-Tune**
   - Start with Phase 1 training
   - Validate at each checkpoint
   - Iterate based on results

5. **Deploy and Monitor**
   - Set up inference server
   - Integrate with API
   - Monitor and improve

---

## Resources

- **LLaMA 3 Documentation:** https://github.com/meta-llama/llama3
- **PEFT Library:** https://github.com/huggingface/peft
- **Alpaca Format Guide:** https://github.com/tatsu-lab/stanford_alpaca
- **Ollama for Inference:** https://ollama.ai
- **Home Assistant API:** https://developers.home-assistant.io/docs/api/rest/

Good luck with your AI training! 🚀
