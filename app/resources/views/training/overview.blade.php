@extends('layout')

@section('title', 'Training Overview')

@section('content')
<div class="card">
    <h2>AI Training Strategy Overview</h2>
    <p style="margin-bottom: 20px;">
        This guide outlines a structured approach to training LLaMA 3 (or similar LLM) to control Home Assistant through the bridge API.
        The training is divided into 4 progressive phases, each building on the previous one.
    </p>
</div>

<div class="grid">
    <div class="card">
        <h2>Phase 1: API Understanding</h2>
        <p style="margin-bottom: 15px;">
            <strong>Duration:</strong> Week 1-2<br>
            <strong>Goal:</strong> Teach the model available endpoints and their functions
        </p>
        <p style="margin-bottom: 15px;">
            Learn to map natural language commands to API calls. Start with simple on/off commands for lights and switches.
        </p>
        <a href="/training/phase1" class="btn btn-primary">View Phase 1 →</a>
    </div>

    <div class="card">
        <h2>Phase 2: Context Awareness</h2>
        <p style="margin-bottom: 15px;">
            <strong>Duration:</strong> Week 3-4<br>
            <strong>Goal:</strong> Handle ambiguity and maintain context
        </p>
        <p style="margin-bottom: 15px;">
            Understand brightness levels, room context, and interpret relative commands like "brighter" or "dimmer".
        </p>
        <a href="/training/phase2" class="btn btn-primary">View Phase 2 →</a>
    </div>

    <div class="card">
        <h2>Phase 3: Multi-Step Reasoning</h2>
        <p style="margin-bottom: 15px;">
            <strong>Duration:</strong> Week 5-6<br>
            <strong>Goal:</strong> Enable complex scenarios requiring multiple API calls
        </p>
        <p style="margin-bottom: 15px;">
            Handle routines like "morning routine" or "leaving house" that require coordinating multiple devices.
        </p>
        <a href="/training/phase3" class="btn btn-primary">View Phase 3 →</a>
    </div>

    <div class="card">
        <h2>Phase 4: State-Aware Decisions</h2>
        <p style="margin-bottom: 15px;">
            <strong>Duration:</strong> Week 7-8<br>
            <strong>Goal:</strong> Make intelligent decisions based on current state
        </p>
        <p style="margin-bottom: 15px;">
            Check current state before actions, handle conditional logic, and provide smart recommendations.
        </p>
        <a href="/training/phase4" class="btn btn-primary">View Phase 4 →</a>
    </div>
</div>

<div class="card">
    <h2>Training Approach</h2>
    <h3>Recommended Method: LoRA (Low-Rank Adaptation)</h3>
    <ul style="margin-left: 20px; line-height: 1.8;">
        <li><strong>Efficient:</strong> Only trains small adapter layers</li>
        <li><strong>Fast:</strong> Trains in hours instead of days</li>
        <li><strong>Preserves:</strong> Keeps base model knowledge</li>
        <li><strong>Flexible:</strong> Easy to switch between tasks</li>
    </ul>

    <h3 style="margin-top: 20px;">Dataset Requirements</h3>
    <ul style="margin-left: 20px; line-height: 1.8;">
        <li>Minimum 1,000 examples per entity type</li>
        <li>Alpaca/Instruction format</li>
        <li>Include variations and edge cases</li>
        <li>Based on your actual Home Assistant entities</li>
    </ul>

    <h3 style="margin-top: 20px;">Validation Metrics</h3>
    <ul style="margin-left: 20px; line-height: 1.8;">
        <li>Single command: >98% accuracy</li>
        <li>Contextual command: >95% accuracy</li>
        <li>Multi-step scenario: >90% accuracy</li>
        <li>Error handling: >85% accuracy</li>
    </ul>
</div>

<div class="card">
    <h2>Quick Start</h2>
    <p style="margin-bottom: 15px;">To begin training your AI assistant:</p>
    <ol style="margin-left: 20px; line-height: 1.8;">
        <li>Generate initial dataset from your actual Home Assistant entities</li>
        <li>Set up training environment (GPU with minimum 24GB VRAM)</li>
        <li>Download LLaMA 3 base model</li>
        <li>Fine-tune using PEFT library with LoRA</li>
        <li>Validate at each checkpoint</li>
        <li>Deploy using Ollama or vLLM</li>
    </ol>
</div>

<div class="card">
    <h2>Resources</h2>
    <ul style="margin-left: 20px; line-height: 1.8;">
        <li><a href="https://github.com/meta-llama/llama3" target="_blank">LLaMA 3 Documentation</a></li>
        <li><a href="https://github.com/huggingface/peft" target="_blank">PEFT Library (for LoRA)</a></li>
        <li><a href="https://github.com/tatsu-lab/stanford_alpaca" target="_blank">Alpaca Format Guide</a></li>
        <li><a href="https://ollama.ai" target="_blank">Ollama (for inference)</a></li>
        <li><a href="/AI_TRAINING_STRATEGY.md" target="_blank">Full Training Strategy Document</a></li>
    </ul>
</div>
@endsection
