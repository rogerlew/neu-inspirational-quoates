

# Inspirational Quotes Web App

A single-page HTML/JS app that generates inspirational quotes using Ollama’s local LLM (`gemma:2b`).  
Categories: **Cringe Gen Z**, **Stoicism**, **Nihilism**, **Pokémon**, **George Washington**.

---

## Prerequisites

- **Python 3.x**  
- **Ollama CLI** (install via Homebrew on macOS)  
- (Optional) Images in an `images/` folder named:
  - `placeholder.jpeg`
  - `cringe.jpeg`
  - `stoic.jpeg`
  - `nihil.jpeg`
  - `pokemon.jpeg`
  - `gwash.jpeg`

---

## Setup

1. **Install Ollama**  
   ```bash
   brew install ollama
   ```

2. **Pull the model**  
   ```bash
   ollama pull gemma:2b
   ```

3. **Start the Ollama API server**  
   ```bash
   ollama serve --port 11434 --cors
   ```
   - Exposes HTTP API at `http://localhost:11434/v1/completions`  
   - `--cors` enables cross-origin requests from your web page.

4. **Clone this repo**  
   ```bash
   git clone https://github.com/yourusername/inspirational-quotes.git
   cd inspirational-quotes
   ```

5. **Serve the static files**  
   ```bash
   python3 -m http.server 8080
   ```
   - Serves at `http://localhost:8080`

6. **Open the app**  
   Point your browser to `http://localhost:8080`

---

## Usage

- Click a category tile to generate a quote.
- Generated quote appears below the tiles.
- Token usage (prompt, completion, total) shows in the footer.
- Make sure `ollama serve` is running while you use the app.

---

## Configuration

- **Model** & **generation parameters** are in the fetch call inside `index.html`:
  ```js
  {
    model: 'gemma:2b',
    prompt: prompts[key],
    max_tokens: 80,
    temperature: 0.8
  }
  ```
- Modify `model`, `max_tokens`, `temperature`, etc., as needed.
- To add new categories, update the `prompts` object and add matching `.tile` and image.

---

## Troubleshooting

- **CORS errors**: ensure you started Ollama with `--cors`.  
- **Port conflicts**: you can change `8080` (web) or `11434` (Ollama) to any free port—just keep them in sync in your commands and in the fetch URL.