# Multi-Scan Mode: User Quick Reference

How to use: Point your device camera at a QR code to scan automatically, or manually type the document number in the field above and press Enter.

---

## Single-Scan Mode (Default, OFF)

### When to Use
- You want to carefully review each document before confirming
- You need to see full document details
- You're handling high-value or sensitive documents
- You want to manually complete or return documents immediately

### How It Works
1. Scan a QR code or type the document number
2. Press **ENTER** or click outside the field
3. Full document details appear (title, location, status, priority, created date)
4. Choose an action:
   - **View Full Details** - Open document in a new tab
   - **Complete** - Mark as completed and archive
   - **Return** - Send back to previous department with remarks
   - **Scan Another Document** - Clear and scan next document

---

## Multi-Scan Mode (ON, Purple Toggle)

### When to Use
- You're processing multiple documents in rapid succession
- You're doing mailroom intake or batch document receiving
- You want continuous scanning without reviewing each document
- You need to process 50+ documents quickly

### How It Works
1. Scan a QR code or type the document number
2. **No need to press ENTER** - system processes automatically
3. A **green checkmark** appears for 0.8 seconds (confirmation)
4. The input field **auto-clears** 0.5 seconds later
5. You're immediately ready to scan the next document
6. **Session statistics** show how many documents you've received

### What You'll See
- **Green checkmark** = Document received successfully ✓
- **Statistics banner** = "5 documents received | 0 errors"
- **Pause banner** (yellow) = Scanner paused after 30 seconds of inactivity

### What Happens on Errors
- **Red warning** (1.5s) = "Not found" or "Connection lost"
- Input field clears automatically
- You can immediately rescan

### Resume from Pause
If the yellow "Scanning paused" banner appears (after 30 seconds of no activity):
- Click the scanner input field
- Scanning resumes and the pause banner disappears

---

## Common Workflows

### Workflow 1: High-Speed Intake (Multi-Scan)
```
Toggle ON (Multi-Scan)
↓
Scan document 1 → Green checkmark → Auto-clear → Ready
Scan document 2 → Green checkmark → Auto-clear → Ready
Scan document 3 → Green checkmark → Auto-clear → Ready
... repeat 50+ times
↓
Done! Check session statistics to verify count
```

### Workflow 2: Careful Review (Single-Scan)
```
Toggle OFF (Single-Scan)
↓
Scan document 1
↓
Review full details: title, location, status, priority
↓
Click "Complete" or "Return" or "Scan Another"
↓
Repeat for next document
```

### Workflow 3: Bulk Processing with Spot Checks
```
Toggle ON (Multi-Scan) and scan 20 documents quickly
↓
Click "View Last Document" to verify the most recent scan
↓
Continue scanning remaining documents
↓
Toggle OFF (Single-Scan) for last few documents that need careful review
```

---

## Tips & Tricks

### Multi-Scan Mode
- **Resume After Pause**: Just click the input field—no need to toggle
- **Check Last Document**: Click "View Last Document" button (opens in new tab)
- **Track Progress**: Watch the statistics banner to see your scan count
- **Fix Errors**: Duplicates get a yellow warning but don't block you—keep scanning!

### Single-Scan Mode
- **Quick Skip**: Click "Scan Another Document" to keep the form open
- **Check Priority**: Look for "PRIORITY" badge to flag urgent documents
- **Batch Operations**: Use modals to complete or return multiple similar documents

### General
- **QR Code or Manual Entry**: You can type document numbers instead of scanning
- **Press ENTER**: In single-scan, always press ENTER or click outside to submit
- **Mobile Users**: Vibration feedback confirms successful scan (if device supports it)

---

## Error Explanations

| Error | What It Means | What To Do |
|-------|---------------|-----------|
| "Not found" | Document doesn't exist in system | Check document number and try again |
| "Connection lost" | Network problem | Wait 1 second (system retries), or refresh page |
| "Duplicate scan detected" | You just scanned this same document within 5 seconds | Intentional duplicate? Continue scanning. Accidental? Next scan will be new. |
| "Scanning too rapidly" | You scanned 3+ documents in less than 2 seconds | Pause 1 second, then continue (system queues your next scan) |
| "Not authorized" | You don't have permission for this document | Contact your supervisor or administrator |

---

## Mode Switching

### What Happens When You Toggle Modes?
- ✓ Current mode switches instantly
- ✓ Scanner input clears
- ✓ Session statistics reset (in multi-scan)
- ✓ All timers reset
- ✓ Your preference is saved (next time you visit, same mode is active)
- ✓ Blue confirmation message appears

### What Happens to Document Details Being Viewed?
- If you're viewing document details in **single-scan** and switch to **multi-scan**: Details stay visible, but new scans will be silent (no pop-ups)
- If you're in **multi-scan** and switch to **single-scan**: Next scan will show full details

---

## Feature Highlights

### What's New in Multi-Scan Mode?

1. **Silent Acknowledgment** - Green checkmark appears, document is received—no interruption
2. **Auto-Clear Input** - Field clears automatically so you never accidentally rescan the same document
3. **Session Counter** - Track your progress without counting manually
4. **Duplicate Detection** - Yellow warning if you scan same document twice in 5 seconds
5. **Rapid Scan Protection** - Automatic pause if scanning too fast (prevents cascading errors)
6. **Haptic Feedback** - Your phone vibrates on successful scan (mobile devices only)

### Why These Features?

- **Silent Acknowledgment** → Speeds up scanning workflow
- **Auto-Clear Input** → Prevents accidental re-scans and frustration
- **Session Counter** → You always know your progress
- **Duplicate Detection** → Catches accidental re-scans without blocking workflow
- **Rapid Scan Protection** → Prevents errors from device tremor or over-enthusiasm
- **Haptic Feedback** → Confirmation you can feel, not just see

---

## Troubleshooting

### Problem: Toggle doesn't stay on after refresh
- **Fix**: Browser localStorage might be disabled. Check browser settings and enable cookies/storage.

### Problem: Vibration not working on mobile
- **Fix**: Some phones require notification permissions. Check device settings. Not all devices support vibration.

### Problem: Too many "duplicate" warnings
- **Fix**: You can scan same document multiple times—the warning is just informational. It won't be counted twice in session statistics.



### Problem: Document details won't appear in single-scan mode
- **Fix**: Make sure you pressed ENTER after typing/scanning the document number. You may need to wait for network to respond (watch for "Processing scan..." message).

---

## Keyboard Shortcuts

- **ENTER** → Submit document number in single-scan mode
- **TAB** → Move between buttons and fields
- **ESC** → Close modals (Complete/Return dialogs)

---

## Summary Table

| Feature | Single-Scan | Multi-Scan |
|---------|------------|-----------|
| **Startup Time** | Default for all users | Opt-in toggle |
| **After Each Scan** | Show full details | Silent + checkmark |
| **Input Auto-Clear** | No | Yes (0.5s delay) |
| **Session Counter** | Not shown | Always visible |

| **Duplicate Warning** | No | Yes (yellow) |
| **Rapid Scan Detection** | No | Yes (auto-pause) |
| **Best For** | Careful review | Fast intake |
| **Typical Scan Rate** | 1 doc every 10-30 seconds | 10+ docs per minute |

---

## Questions?

For more detailed technical information, see: **MULTI_SCAN_IMPLEMENTATION.md**

For support, contact your administrator or supervisor.
