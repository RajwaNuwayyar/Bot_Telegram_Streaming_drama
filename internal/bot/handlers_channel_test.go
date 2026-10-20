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
		expectedCats  []string
	}{
		{
			caption:       "The Secret CEO - Episode 01",
			expectedTitle: "The Secret CEO",
			expectedEp:    1,
			expectedVIP:   false,
			expectedCats:  nil,
		},
		{
			caption:       "[My Billionaire Husband] Part 3 #vip",
			expectedTitle: "My Billionaire Husband",
			expectedEp:    3,
			expectedVIP:   true,
			expectedCats:  nil,
		},
		{
			caption:       "Cinta Sejati Sang Bos Ep 5",
			expectedTitle: "Cinta Sejati Sang Bos",
			expectedEp:    5,
			expectedVIP:   false,
			expectedCats:  nil,
		},
		{
			caption:       "Judul: Balas Dendam Mantan\nEpisode: 12\nStatus: VIP",
			expectedTitle: "Balas Dendam Mantan",
			expectedEp:    12,
			expectedVIP:   true,
			expectedCats:  nil,
		},
		{
			caption:       "Short Drama Eps. 7 [VIP]",
			expectedTitle: "Short Drama",
			expectedEp:    7,
			expectedVIP:   true,
			expectedCats:  nil,
		},
		{
			caption:       "Gacoan - Episode 01 #Romantis",
			expectedTitle: "Gacoan",
			expectedEp:    1,
			expectedVIP:   false,
			expectedCats:  []string{"Romantis"},
		},
		{
			caption:       "Gacoan - Episode 02 #vip #Romantis #Komedi",
			expectedTitle: "Gacoan",
			expectedEp:    2,
			expectedVIP:   true,
			expectedCats:  []string{"Romantis", "Komedi"},
		},
	}

	for _, tt := range tests {
		title, ep, isVIP, cats := parseCaption(tt.caption)
		if title != tt.expectedTitle {
			t.Errorf("parseCaption(%q) title = %q, expected %q", tt.caption, title, tt.expectedTitle)
		}
		if ep != tt.expectedEp {
			t.Errorf("parseCaption(%q) ep = %d, expected %d", tt.caption, ep, tt.expectedEp)
		}
		if isVIP != tt.expectedVIP {
			t.Errorf("parseCaption(%q) isVIP = %v, expected %v", tt.caption, isVIP, tt.expectedVIP)
		}
		for _, ec := range tt.expectedCats {
			found := false
			for _, ac := range cats {
				if ac == ec {
					found = true
					break
				}
			}
			if !found {
				t.Errorf("parseCaption(%q) expected category %q in %v", tt.caption, ec, cats)
			}
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

