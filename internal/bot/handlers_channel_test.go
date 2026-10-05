package bot

import (
	"testing"
)

func TestParseCaption(t *testing.T) {
	tests := []struct {
		caption       string
		expectedTitle string
		expectedEp    int
		expectedVIP   bool
	}{
		{
			caption:       "The Secret CEO - Episode 01",
			expectedTitle: "The Secret CEO",
			expectedEp:    1,
			expectedVIP:   false,
		},
		{
			caption:       "[My Billionaire Husband] Part 3 #vip",
			expectedTitle: "My Billionaire Husband",
			expectedEp:    3,
			expectedVIP:   true,
		},
		{
			caption:       "Cinta Sejati Sang Bos Ep 5",
			expectedTitle: "Cinta Sejati Sang Bos",
			expectedEp:    5,
			expectedVIP:   false, // VIP flag will be calculated based on ep > 2
		},
		{
			caption:       "Judul: Balas Dendam Mantan\nEpisode: 12\nStatus: VIP",
			expectedTitle: "Balas Dendam Mantan",
			expectedEp:    12,
			expectedVIP:   true,
		},
		{
			caption:       "Short Drama Eps. 7 [VIP]",
			expectedTitle: "Short Drama",
			expectedEp:    7,
			expectedVIP:   true,
		},
	}

	for _, tt := range tests {
		title, ep, isVIP := parseCaption(tt.caption)
		if title != tt.expectedTitle {
			t.Errorf("parseCaption(%q) title = %q, expected %q", tt.caption, title, tt.expectedTitle)
		}
		if ep != tt.expectedEp {
			t.Errorf("parseCaption(%q) ep = %d, expected %d", tt.caption, ep, tt.expectedEp)
		}
		if isVIP != tt.expectedVIP {
			t.Errorf("parseCaption(%q) isVIP = %v, expected %v", tt.caption, isVIP, tt.expectedVIP)
		}
	}
}

func TestParsePosterTitle(t *testing.T) {
	tests := []struct {
		caption       string
		expectedTitle string
	}{
		{
			caption:       "GrandBlue #poster",
			expectedTitle: "GrandBlue",
		},
		{
			caption:       "Charlotte #poster",
			expectedTitle: "Charlotte",
		},
		{
			caption:       "[Grand Blue] #poster",
			expectedTitle: "Grand Blue",
		},
		{
			caption:       "Judul: Charlotte\nTag: #poster",
			expectedTitle: "Charlotte",
		},
		{
			caption:       "GrandBlue - Poster #thumbnail",
			expectedTitle: "GrandBlue",
		},
	}

	for _, tt := range tests {
		title := parsePosterTitle(tt.caption)
		if title != tt.expectedTitle {
			t.Errorf("parsePosterTitle(%q) = %q, expected %q", tt.caption, title, tt.expectedTitle)
		}
	}
}

