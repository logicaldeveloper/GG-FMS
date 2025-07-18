from flask import Flask, request, jsonify, render_template
import json
import os
from datetime import datetime

app = Flask(__name__)
DATA_FILE = 'expenses.json'

# Ensure the data file exists
if not os.path.exists(DATA_FILE):
    with open(DATA_FILE, 'w') as f:
        json.dump([], f)  # Initialize with an empty list   

def load_expenses():
    if not os.path.exists(DATA_FILE):
        return []  # Return empty list if file doesn't exist
    with open(DATA_FILE, 'r') as f:
        try:
            data = json.load(f)
            if isinstance(data, list):
                return data
            else:
                return []  # If data is corrupt or not a list, start fresh
        except json.JSONDecodeError:
            return []  # If file is empty or invalid JSON

def save_expenses(data):
    with open(DATA_FILE, 'w') as f:
        json.dump(data, f, indent=2)

@app.route('/static/<path:path>')
def static_files(path): 
    return app.send_static_file(path)   

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/dashboard')
def dashboard():
    expenses = load_expenses()
    return render_template('dashboard.html', expenses=expenses)


@app.route('/add', methods=['POST'])
def add_expense():
    data = request.json
    payment_mode = data.get('type')

    # Assign category
    if payment_mode in ['UPI', 'Debit Card']:
        data['category'] = 'Savings Account Expenses'
    elif payment_mode in ['UPI CC', 'Credit Card']:
        data['category'] = 'Credit Expenses'
    else:
        data['category'] = 'Other'

    expenses = load_expenses()
    data['id'] = len(expenses) + 1
    expenses.append(data)
    save_expenses(expenses)
    return jsonify({"status": "success"})

@app.route('/get_all', methods=['GET'])
def get_expenses():
    return jsonify(load_expenses())

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)