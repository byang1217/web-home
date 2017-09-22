//
//  MyLib.h
//
//  Created by Bin Yang on 6/15/16.
//  Copyright © 2016 Bin Yang. All rights reserved.
//

#import <UIKit/UIKit.h>
#import <CoreLocation/CoreLocation.h>

@import Photos;

#import "XMLReader.h"
#import "Reachability.h"

#define BACKGROUND_SESSION_TIMEOUT (61)
#define UPLOAD_MAX_LENGTH   (2*1024*1024)
#define MAX_ERROR_RETRY     3
#define MAX_ERROR_RETENTION (5*60)

#if __has_include("MyLib.h")
#define MYLOG_LEVEL     2 //0: panic (without log) only, 1: error, 2: info 3:debug

#define MyLog(t, s, ...)    NSLog( @"<%@><%@:%s:%d> %@", t, [[NSString stringWithUTF8String:__FILE__] lastPathComponent], __PRETTY_FUNCTION__, __LINE__,  [NSString stringWithFormat:(s), ##__VA_ARGS__] )

#if (MYLOG_LEVEL > 0)
#define MyPanic(s...)       do{MyLog(@"Panic", s); [NSException raise:@"Panic Exception" format:@" Reason"];}while(0)
#else
#define MyPanic(s...)       do{[NSException raise:@"Panic Exception" format:@" Reason"];}while(0)
#endif

#if (MYLOG_LEVEL > 0)
#define MyErr(s...)         do{MyLog(@"Err", s);}while(0)
#else
#define MyErr(s...)         do{;}while(0)
#endif
#if (MYLOG_LEVEL > 1)
#define MyInf(s...)         do{MyLog(@"Inf", s);}while(0)
#else
#define MyInf(s...)         do{;}while(0)
#endif
#if (MYLOG_LEVEL > 2)
#define MyDbg(s...)         do{MyLog(@"Dbg", s);}while(0)
#else
#define MyDbg(s...)         do{;}while(0)
#endif

#define IS_OS_8_OR_LATER ([[[UIDevice currentDevice] systemVersion] floatValue] >= 8.0)
#define IS_OS_9_OR_LATER ([[[UIDevice currentDevice] systemVersion] floatValue] >= 9.0)

#define LOCAL_STRING(str)      NSLocalizedStringFromTable(str, @"InfoPlist", nil)

/*
#define fequal(a,b) (fabs((a) - (b)) < FLT_EPSILON)
#define fequalzero(a) (fabs(a) < FLT_EPSILON)
#define flessthan(a,b) (fabs(a) < fabs(b)+FLT_EPSILON)
*/
 
typedef void(^CompletionBlockCallback)(id result, BOOL error);


/*
@protocol MyLib_UploadDelegate <NSObject>
-(void)uploadCompleteWithError:(NSError *)error;
-(void)uploadReceiveData:(NSData *)data;
-(void)uploadBecomeInvalidWithError:(NSError *)error;
-(void)uploadFinishGackgoundSession;
@end
*/

@interface MyLib : NSObject

- (id)init;

/* common */
@property dispatch_queue_t asyncWorkQueue;


/*
 Photo Functions
 */
@property NSMutableArray *allAssetArray;
@property NSMutableArray *imageAssetArray;
@property NSMutableArray *videoAssetArray;
@property NSMutableDictionary *SmartAlbumsDict;
@property NSMutableDictionary *UserAlbumsDict;

- (void)initPHPhotoLib;
- (PHAsset *) loadAssetWithId:(NSString *)LocalIdentifier;
- (NSMutableDictionary *)fechPHAssetData:(PHAsset *)asset;
- (void) clearAlbums;
- (void) loadAlbums:(NSDate *)imageFromDate videoFromDate:(NSDate *)videoFromDate;

/* network */
@property BOOL isNetWorkWiFi;
@property BOOL isNetWorkWWAN;
- (void)initReachbility;
- (void)updateInterfaceWithReachability:(Reachability *)reachability;

/*
 Upload functions
 */
@property BOOL isBackgroundSessionReady;
@property NSURLSession *sessionForeground;
@property NSURLSession *sessionBackground;

- (void)initUrlSession:(NSInteger)timeout;
- (void)initBackgroundSession:(NSString *)name timeout:(NSInteger)timeout;
- (void)cancelAllBackgroundTasks;
- (void)cancelAllForgroundUploadTasks;
- (BOOL)postDataBackground:(NSData *)data urlString:(NSString *)urlString urlParams:(NSDictionary *)urlParams;
- (BOOL)getDataBackground:(NSString *)urlString urlParams:(NSDictionary *)urlParams;
- (BOOL)postDataForeground:(NSData *)data urlString:(NSString *)urlString urlParams:(NSDictionary *)urlParams completionHandler:(void (^)(NSData *data, NSURLResponse *response, NSError *error))completionHandler progressHandler:(void (^)(int64_t totalBytesSent))progressHandler;

/*
 Location Functions
 */
- (void)startLocationMonitor;
- (void)startLocationUpdate;
- (void)stopLocationUpdate;



////////////////////////// C functions /////////////////////////////////
NSString *MyLib_DeviceUUID(void);

NSString *MyLib_DateString_YMDHms(NSDate *date);
NSString *MyLib_DateString_YM(NSDate *date);

NSDate *MyLib_StringDate(NSString *string);
NSString *MyLib_UnixTimeUsString(void);
NSUInteger MyLib_UnixTimeUs(void);

NSString *MyLib_DataMD5(NSData *data);

NSMutableDictionary *MyLib_SimpleXMLDict(NSData *xmlData);

UIImage *MyLib_ImageWithImage(UIImage *image, CGSize newSize);

NSString *base64_encode(NSString *str);
NSString *base64_decode(NSString *str);

NSMutableDictionary *json_to_dict(NSString *strJson);

/* C functions */


NSString* MyLib_LogFilePath(void);
void MyLib_Log2File(void);

@end

extern MyLib *defaultMyLib;

#endif //MyLib.h