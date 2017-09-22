//
//
//  Created by Bin Yang on 6/15/16.
//  Copyright © 2016 Bin Yang. All rights reserved.
//

#import <UIKit/UIKit.h>
#import "MyLib.h"
#import "SetupView.h"
#import "MyMusicPlayer.h"
#import "ShortBeat.h"
#import "LongBeat.h"

enum {
    STATUS_ERROR,
    STATUS_UPLOADING,
    STATUS_DONE,
};

@protocol AlbumSyncDelegate <NSObject>
-(void)statusUpdateHandler:(NSDictionary *)statusInfDict;
@end


@interface AlbumSync : MyLib

@property(nonatomic,assign) id<AlbumSyncDelegate>delegate;

/*config */
@property NSString *uploadServerURLString;
- (BOOL) scanHandle:(NSString *)str errString:(NSString **)errString;

@property UIBackgroundTaskIdentifier bgTask;

/* status */
@property BOOL stopPending;
@property BOOL uploadComplete;
@property BOOL uploadOnGoing;
@property BOOL photoLibChangePending;
@property BOOL reachabilityChangePending;

/* music */
@property MyMusicPlayer *musicPlayer;


- (void)handshakeWithToken;
- (void)uploadData:(NSData *)data params:(NSDictionary *)params;
- (NSDictionary *)getStatusInfoDict;

@end

extern AlbumSync *defaultAlbumSync;
void AlbumSync_init(void);
void AlbumSync_Start(void);
void AlbumSync_reset(void);
void AlbumSync_exeOnActionWorkQueue(void (^exeHandler)(void));

