# Lorenzo Calvisi, mat. 634171 
#
# Progetto finale di Linguistica Computazionale, a.a. 2022/2023
#
# PROGRAMMA N. 2
from nltk.probability import FreqDist
from collections import OrderedDict
from nltk import ne_chunk
from nltk.tokenize import word_tokenize, sent_tokenize
from nltk.tag import pos_tag
from nltk.util import ngrams
from math import log2
import sys
import os
# Variabili globali: liste dei tag indicanti nomi, aggettivi e avverbi
noun_pos_list = ['NN','NNP','NNS'] # Lista dei tag di tipo nome
adj_pos_list = ['JJR','JJS'] # Lista dei tag di tipo aggettivo 
adv_pos_list = ['RB', 'RBR', 'RBS', 'WRB'] # Lista dei tag di tipo avverbio

class EmptyFile(Exception): 
    """
    Eccezione sollevata qualora il file di input sia vuoto
    """

def extract_adj_noun(tagged_tokens): # Funzione che calcola la lista dei bigrammi aggettivo-sostantivo
    '''
    Funzione per estrarre le coppie di aggettivo-nome.

    Prende in input un insieme di token taggati e restituisce una lista di tuple formate da aggettivo e sostantivo. 
    '''
    adj_noun_bigrams = list()

    for i in range(len(tagged_corpus) - 1):
        if tagged_tokens[i][1].startswith('J') and tagged_tokens[i+1][1].startswith('N'):
            adj_noun_bigrams.append((tagged_tokens[i][0],tagged_tokens[i+1][0]))

    return adj_noun_bigrams

def get_pos_list(tagged_tokens): # Funzione che estrae i PoS dalla lista dei token taggati
    """
    Funzione per l'estrazione dei PoS dal corpus taggato.

    Prende in input un corpus taggato e restituisce una lista contenente i PoS.
    """
    return [entry[1] for entry in tagged_tokens] # Per ogni entry di tagged_tokens, aggiunge alla lista il secondo elemento della tupla corrente, ossia la PoS corrente.

def pos_ngrams_freq(tagged_tokens, n, m): # Funzione che calcola le frequenze degli "m" n-grammi di PoS più comuni
    """
    Funzione per il calcolo delle frequenze dei PoS.

    La funzione prende in input un valore n > 0 che viene usato per calcolare gli n-grammi di PoS di cui ricavare poi la frequenza.

    Restituisce una lista di tuple contentente gli "m" n-grammi ordinati in modo decrescente secondo la frequenza.

    """

    pos_list = get_pos_list(tagged_tokens) # Ottengo la lista dei PoS dal corpus taggato
    ngrams_pos_list = list(ngrams(pos_list,n)) # Estraggo gli n-grammi e li converto in una lista di tuple
    
    return FreqDist(ngrams_pos_list).most_common(m)

def most_common_tokens_by_pos(tagged_tokens, pos_list, n='MAX'): # Funzione che calcola gli n token più comuni di un certo tipo
    """
    Funzione che restituisce gli n token più comuni tra quelli taggati come in pos_list.

    Prende in input la lista dei token taggati, la lista dei PoS e il numero n di token da mostrare.

    Qualora n venga omesso, si assume che abbia valore 'MAX', per cui vengono mostrati tutti i token relativi ai tag indicati.
    
    Il valore restituito è un dizionario di liste di tuple, in cui i PoS fanno da chiave, e in cui i valori sono liste contenenti tuple di due elementi, ovvero il token con la relativa frequenza.

    In questo contesto, qualora il PoS Tag indicasse un nome, un avverbio o un aggettivo, non volendo far distinzioni tra nomi, aggettivi o avvebri di vario tipo, viene aggiunto rispettivamente alla chiave NOUN, ADJ o ADV. 

    Le liste di PoS Tag indicanti nomi, aggettivi o avverbi sono indicate all'inizio del programma come variabili globali.
    """
    token_dict = {} # Creo un dizionario
    # Non voglio distinguere i vari nomi: tutto ciò che è taggato con uno dei tag elencatiin noun_pos_list deve essere considerato semplicemente "NOUN"
    # Stesso dicasi per aggettivi e avverbi.

    for pos in pos_list:
        if pos in noun_pos_list: # Se il pos si trova in questa lista
            pos_index = 'NOUN' # Allora sto esaminando un nome
        elif pos in adj_pos_list: # Se si trova in questa
            pos_index = 'ADJ' # Sto esaminando un aggettivo
        elif pos in adv_pos_list: # Se si trova in questa
            pos_index = 'ADV' # Sto esaminando un avverbio
        else:
            pos_index = pos
        token_dict[pos_index] = [] # Dopo aver "corretto" opportunamente le chiavi, aggiungo un nuovo elemento di tipo lista al dizionario

    for token, pos in tagged_tokens: # Riempio il dizionariio con i token che sono stati taggati con i PoS contenuti in pos_list 
        if pos in pos_list: # Verifico che la prima lettera del pos corrente sia contenuta nella lista
            if pos in noun_pos_list: # Se il pos si trova in questa lista
                pos_index = 'NOUN' # Allora sto esaminando un nome
            elif pos in adj_pos_list: # Se si trova in questa
                pos_index = 'ADJ' # Sto esaminando un aggettivo
            elif pos in adv_pos_list: # Se si trova in questa
                pos_index = 'ADV' # Sto esaminando un avverbio
            else:
                pos_index = pos # In tutti gli altri casi, lascio che il pos_index sia uguale al pos letto
            token_dict[pos_index].append(token) # Aggiungo il token corrente alla lista corretta

    most_common_tokens = {} # Creao un array associativo vuoto

    for pos, tokens in token_dict.items(): # Per ogni pos e tokens che si trova in token_dict
        if len(tokens) == 0: # Se non vi sono token corrispondenti a quel PoS, elimino la chiave
            del pos
        else:
            if n == 'MAX':
                most_common_tokens[pos] = FreqDist(tokens).most_common() # Calcolo gli n token più comuni e li pongo in ordine decrescente entro l'elemento pos corrispondente
            else:
                most_common_tokens[pos] = FreqDist(tokens).most_common(int(n))
    return most_common_tokens

def max_conditional_prob_adj_noun(adj_noun_bigrams, n): # Funzione che calcola gli n bigrammi (aggettivo, sostantivo) dotati di massima probabilità condizionta
    """
    Funzione che calcola gli n bigrammi composti da aggettivo e sostantivo dotati di probabilità condizionata massima.

    La funzione prende in input una lista di tuple composte da aggettivo e sostantivo, di quelle restituite dalla funzione extract_adj_noun(), e il valore di n.

    Qualora n venga omesso, assume valore  "MAX", e vengono mostrati tutti i bigrammi aggettivo-sostantivo, ordinati in ordine decrescente secondo la loro probabilità condizionata.

    La formula della probabilità condizionata P(N|A) = P(A, N) / P(A) viene approssimata a P(N|A) = F(A, N) / F(A), dove
        * P(N|A) => Probabilità d'incontrare un nome dato un aggettivo (probabilità condizionata)
        * P(A, N) => Probabilità d'incontrare un bigramma (aggettivo, nome)
        * P(A) => Probabilità d'incontrare un aggettivo

        * F(A, N) => Frequenza assoluta di un bigramma (aggettivo, nome)
        * F(A) => Frequenza assoluta di un aggettivo

    Per la dimostrazione della validità di P(N|A) = F(A, N) / F(A), si rimanda al libro di A. Lenci, S. Montemagni, V. Pirelli, "Testo e computer", Carrocci, 2016, pg. 168 
    """
    
    adj_noun_freq = FreqDist(adj_noun_bigrams) # Calcolo la frequenza dei bigrammi

    adjectives  = [adj for adj,noun in adj_noun_bigrams] # Estraggo gli aggettivi dal corpus e ne calcolo la frequenza

    adj_freq = FreqDist(adjectives) # Ne calcolo la frequenza

    cond_p = {} # Creo un dizionario che, per ogni bigramma, conterrà la probabilità condizionata
    for bigram, freq in adj_noun_freq.most_common(): # Per ogni bigramma con rispettiva frequenza
        adj = bigram[0] # Estraggo l'aggettivo
        cond_p[bigram] = freq / adj_freq[adj] # Divido la frequenza del bigramma per la frequenza dell'aggettivo corrispondente

    sorted_dict = OrderedDict(sorted(cond_p.items(), reverse=True, key=lambda x: x[1])) # Ordino il dizionario
    sorted_list = list(sorted_dict.items()) # Lo salvo in una lista
    if n == 'MAX': # Se n è uguale a MAX - o non viene specificata 
        n = len(sorted_list) # Le assegno il valore della lunghezza della lista, sì da restituire la lista intera
    return sorted_list[:n] # Restituisco il numero di elementi richiesti

def pointwise_mutual_information(first_token, second_token, corpus_tokens): # Funzione che calcola la pmi di due token
    """
    Funzione che calcola la Pointwise Mutual Information di due token, dato un corpus di riferimento
    
    La formula adoperata è PMI = log2( f(a, n) * |C| / f(a) * f(n)) la cui dimostrazione si può trovare in Lenci et alii, "Testo e computer", Carocci, 2016, pg. 201

    Dopo aver calcolato la dimensione del corpus - |C| - se ne estraggono i bigrammi dal corpus, e se ne calcola la frequenza in questo - f(a, n).
    Viene poi calcolata la frequenza nel corpus di ciascun elemento del bigramma - f(a), f(n).

    Il calcolo viene effettuato a questo punto secondo la formula summenzionata.

    """
    bigrams = list(ngrams(corpus_tokens,2)) # Calcolo dei bigrammi
    corpus_dim = len(corpus_tokens) # Calcolo dimensione del corpus
    
    first_token_freq = corpus_tokens.count(first_token) # Frequenza del primo token
    second_token_freq = corpus_tokens.count(second_token) # Frequenza del secondo token
    first_second_token_bigram_freq = bigrams.count((first_token, second_token)) # Frequenza del bigramma
    
    pmi = log2((first_second_token_bigram_freq*corpus_dim) / (first_token_freq * second_token_freq)) # Calcolo della PMI

    return pmi

def media_distribuzione_frequenza_token(tokenized_sentence, tokenized_corpus): # Funzione per il calcolo della media della distribuzione di frequenza dei token
    """
    Funzione per il calcolo della media della distribuzione di frequenza dei token di una frase.

    Prende in input una frase tokenizzata e un corpus tokenizzato di riferimento.

    Di ogni sua parola viene calcolata la frequenza all'interno del corpus. Le frequenze vengono poi sommate, e la somma viene divisa per la lunghezza della frase in token.
    """
    sum_freq = 0
    for word in tokenized_sentence: # Per ogni parola nella frase
        sum_freq += tokenized_corpus.count(word) # Calcolo la somma delle frequenze nel corpus dei token della frase
    media_dist = sum_freq / len(tokenized_sentence) # Calcolo la media di distribuzione dvidendo quel valore per la lunghezza della frase
    return media_dist # Restituisco la media della distribuzione

def markov2(sentence, tokenized_corpus): # Modello markoviano ordine 2
    """
    Funzione per il calcolo della probabilità di una frase con un modello markoviano del secondo ordine.
    
    La funzione prende in input una frase e il corpus tokenizzato di cui fa parte.
    
    Dopo aver estratto i trigrammi della frase, i trigrammi e i bigrammi del corpus, e aver calcolato la distribuzione di frequenza di questi, la probabilità di ciascun trigramma della frase viene calcolata dividendo la sua frequenza nel corpus per la frequenza nel corpus del bigramma costituito dai primi due elementi.
    """
    # Operazioni sulla frase
    tokenized_sentence = word_tokenize(sentence) # Tokenizzo ciascuna frase
    sent_trigrams = list(ngrams(tokenized_sentence, 3)) # Estraggo i trigrammi dalla frase

    # Operazioni sul corpus
    dim_corpus = len(tokenized_corpus) # Calcolo la dimensione del corpus
    trigrams = list(ngrams(tokenized_corpus, 3)) # Estraggo i trigrammi dal corpus
    bigrams = list(ngrams(tokenized_corpus, 2)) # Estraggo i bigrammi dal corpus
    freq_dist_trigr = FreqDist(trigrams) # Calcolo la frequenza dei trigrammi
    freq_dist_bigr = FreqDist(bigrams) # Calcolo la frequenza dei bigrammi 
    first_word = tokenized_sentence[0] # Estraggo il primo token dalla frase
    
    prob_1 = tokenized_corpus.count(first_word)/ dim_corpus # Calcolo la probabilità della first_word rispetto al corpus
    prob_2 = bigrams.count(sent_trigrams[0][:2]) / tokenized_corpus.count(sent_trigrams[0][0])
    prob_frase = prob_1*prob_2
        
    for trigram in sent_trigrams: # Per ogni trigramma della frase
        trigram_freq = freq_dist_trigr[trigram] # Ne estraggo la distribuzione di frequenza
        bigram_freq = freq_dist_bigr[trigram[:2]] # Faccio lo stesso per il bigramma corrispondente ai primi due elementi del trigramma
        trigram_prob = trigram_freq / bigram_freq # Calcolo la probabilità come rapporto tra le frequenze su calcolate, ed aggiungo 1 per il Laplace-Smoothing
        prob_frase *= trigram_prob # Moltiplico la probabilità del trigramma corrente per il valore di probabilità calcolato finora
    return prob_frase
        
try:
    input_path = sys.argv[1] # File da aprire
    file_input = open(input_path, "r") # Apro un file da riga di comando
except IndexError:
    print("Non hai specificato il file\n")
    print(sys.exc_info())
except FileNotFoundError:
    print("Il file non è stato trovato\n")
    print(sys.exc_info())
except PermissionError:
    print("Non hai i permessi per leggere il file", input_path,"\n")
    print(sys.exc_info())
else:
    try:
        input_file_name = os.path.basename(input_path) # Dal percorso fornito, ricavo il nome del file
        output_file_name = "programma_2_output_{}.txt".format(input_file_name) # Costruisco la stringa del nome del file di output
        output_file = open(output_file_name, "w") # Apro un file di output
    except PermissionError:
        print("Non hai i permessi di scrittura per scrivere sul file di output\n")
        print(sys.exc_info())
    else:
        try:
            corpus = file_input.read()
            if len(corpus) == 0:
                raise(EmptyFile)
        except EmptyFile:
            print("Il file è vuoto\n")
        else:
            token_sent = sent_tokenize(corpus)

            token_words = word_tokenize(corpus) # Lo tokenizzo

            tagged_corpus = pos_tag(token_words) # Taggo il corpus

            # Punto 1: Estrazione ordinata per frequenza decrescente, con relativa frequenza
            #   * 1a: 10 PoS, bigrammi PoS e trigrammi PoS più frequenti
            output_file.write("############# {} #############\n\n".format(sys.argv[1]))
            output_file.write("################# 1a. PoS, Bigrammi PoS e Trigrammi PoS più frequenti ######################\n")
            base = "grammi" 
            n = 0 # La n di n-grammi
            m = 10 # Numero di n-grammi pos da mostrare

            for prefix in ["mono", "bi", "tri"]: # Lista di prefissi
                elemento = prefix+base # Concateno la base con il prefisso per formare la parola corretta
                output_file.write("{} {} PoS più comuni:\n".format(m, elemento)) # Stampo la scritta "m n-grammi PoS più comuni" (es. 10 bigrammi PoS più comuni)
                n+=1 # Incremento la n
                for pos_ngram,freq in pos_ngrams_freq(tagged_corpus, n, m): # Per ogni elemento della lista degli m n-grammi più comuni
                    output_file.write("{} freq: {}\n".format(pos_ngram, freq)) # Stampo l'n-gramma corrispondente (una tupla) seguito dall'indicazione della frequenza (un intero, secondo elemento della tupla più esterna)
                output_file.write("\n")

            #   * 1b: 20 Sostantivi, Avverbi e Aggettivi più frequenti
            name_list = ['NN','NNP','NNS'] # Lista dei pos dei nomi
            adj_list = ['JJR','JJS'] # Lista dei pos degli aggettivi
            adv_list = ['RB', 'RBR', 'RBS', 'WRB'] 

            pos_list = name_list + adj_list + adv_list # Lista completa dei PoS

            common_tokens = most_common_tokens_by_pos(tagged_corpus, pos_list, 20)

            output_file.write("############# 1b. Token più comuni ########################\n")

            for pos,elem in common_tokens.items():
                output_file.write("{}:\n".format(pos))
                for token,freq in elem:
                    output_file.write("\t\"{}\", frequenza: {}\n".format(token, freq))
                    
            # Punto 2: Estrazione dei bigrammi (Aggettivo, Sostantivo)
            adj_noun_bigrams = extract_adj_noun(tagged_corpus) # Estraggo i bigrammi di aggettivi e nomi

            #   * 2a: Calcolo e stampa dei 20 più frequenti
            adj_noun_count = FreqDist(adj_noun_bigrams) # Ne calcolo i 20 più comuni
            output_file.write("\n##################### 2a. Coppie di aggettivo-sostantivo più comuni ####################################\n")

            for bigram,freq in adj_noun_count.most_common(20):
                output_file.write("{} frequenza: {}\n".format(bigram, freq))

            #   * 2b: 20 con probabilità condizionata massima, e relativo valore di probabilità
            output_file.write("\n#################### 2b. Coppie di aggettivo-sostantivo con probabilità condizionata massima ####################\n")

            adj_noun_max_cond_prob = max_conditional_prob_adj_noun(adj_noun_bigrams,20)

            for bigram,prob in adj_noun_max_cond_prob:
                output_file.write("{}, => {}\n".format(bigram, prob))

            #   * 2c: 20 con Pointwise Mutual Information (PMI) massima, e relativa PMI
            output_file.write("\n###################### 2c. 20 coppie con massima Pointwise Mutual Information #################\n")

            pmi_list = []

            for adj, noun in adj_noun_bigrams:
                current_pmi = pointwise_mutual_information(adj, noun, token_words)  # Calcolo la PMI del bigramma corrente
                current_bigram = (adj, noun)
                pmi_list.append((current_bigram, current_pmi))  # Aggiungo il bigramma corrente e la PMI alla lista

            pmi_list.sort(key=lambda x: x[1], reverse=True)  # Ordino la lista secondo il valore delle PMI

            for bigram, pmi in pmi_list[:20]:
                output_file.write("{} PMI: {}\n".format(bigram, pmi))


            # Punto 3: Considerate le frasi con una lunghezza compresa tra 10 e 20 token, in cui almeno la metà dei token occorre almeno 2 volte nel corpus (i.e., non è un hapax)"
            output_file.write("\n###################### 3a. Frase con la media della distribuzione di frequenza più alta #################\n")
            hapaxes = FreqDist(token_words).hapaxes()  # Calcolo gli hapax del corpus
            compliant_sentences = []  # Lista che raccoglie le frasi che rispettano i criteri della richiesta

            # Selezione delle frasi idonee
            for sent in token_sent:  # Per ogni frase
                sent_l = len(word_tokenize(sent))  # Ne calcolo la dimensione in token
                if sent_l in range(10, 20):  # Se il numero di token è compreso tra 10 e 20
                    n_hapaxes = 0  # Inizializzo il contatore degli hapax
                    for word in sent:  # Per ogni parola che compone la frase in esame
                        if word in hapaxes:  # Se è un hapax
                            n_hapaxes += 1  # Aumento il contatore
                    if n_hapaxes < (sent_l / 2):  # Se il numero di hapax della frase corrente è minore della metà della lunghezza della frase stessa
                        compliant_sentences.append(sent)  # Allora i criteri sono stati tutti rispettati: aggiungo la frase alla lista

            medie_dist_freq = []  # Lista contenente le medie di distribuzione di frequenza

            for sent in compliant_sentences:  # Per ogni frase in compliant_sentences
                media_dist_freq_token = media_distribuzione_frequenza_token(sent, token_words)  # Calcolo la media di distribuzione di frequenza dei token
                medie_dist_freq.append((sent, media_dist_freq_token))  # Aggiungo il valore corrispondente alla lista delle medie di distribuzione di frequenza

            max_dist_freq = max(medie_dist_freq, key=lambda media: media[1])  # Estraggo la tupla con la media di distribuzione maggiore
            output_file.write("{} => {}\n".format(max_dist_freq[0], max_dist_freq[1]))


            output_file.write("\n###################### 3b. Frase con la media della distribuzione di frequenza più bassa #################\n")
            min_dist_freq = min(medie_dist_freq, key=lambda media: media[1])  # Estraggo la tupla con la media di distribuzione minore
            output_file.write("{} => {}\n".format(min_dist_freq[0], min_dist_freq[1]))


            output_file.write("\n###################### 3c. Frase con la probabilità più alta secondo un modello di Markov di ordine 2 #################\n")
            markov_prob_list = [(sent, markov2(sent, token_words)) for sent in compliant_sentences]  # Lista contenente le probabilità associate ad ogni frase
            max_prob = max(markov_prob_list, key=lambda x: x[1])  # Trovo la frase con probabilità maggiore
            output_file.write("{} => {}\n".format(max_prob[0], max_prob[1]))


            # Punto 4: Estratte le Entità Nominate del testo, identificare per ciascuna classe di NE i 15 elementi più frequenti, ordinati per frequenza decrescente e con relativa frequenza.\n"
            output_file.write("\n###################### 4. 15 elementi più frequenti per ogni categoria di Named Entity #################\n")
            named_entities = ne_chunk(tagged_corpus)  # Named Entity recognition
            classified_entities = {}
            freq_dist_entities = {}

            for entity in named_entities:
                if hasattr(entity, 'label'):  # Se ha una label (ossia: è una NE)
                    ne_label = entity.label()  # Salvo la label
                    leaves = entity.leaves()  # Salvo le foglie
                    classified_entities.setdefault(ne_label, [])  # Inizializzo un dizionario di liste
                    classified_entities[ne_label].extend([token for token, pos in leaves])  # Uso il metodo extend invece di append per ottenere una lista semplice di valori invece di una lista di liste

            for ne_class in classified_entities.keys():  # Per ogni classe di NE tra quelle presenti nelle chiavi del dizionario
                freq_dist_entities[ne_class] = FreqDist(classified_entities[ne_class])  # Calcolo la frequenza di ciascuna sua entità
                output_file.write("{}\n".format(ne_class))
                for ne, frequency in freq_dist_entities[ne_class].most_common(15):  # Per ciascuna categoria, ne stampo i 15 più comuni con relativa frequenza
                    output_file.write("{}, frequenza: {}\n".format(ne, frequency))
                output_file.write("\n")
            output_file.close()